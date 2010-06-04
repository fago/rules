<?php
// $Id$

/**
 * @file
 * Rules base classes and interfaces needed for any rule evaluation.
 */

//TODO: add schedule routine that works like executing, but takes a $date as first argument.
//TODO: Make variables() info methods optionally return properly translated strings.

/**
 * Make sure loaded rule configs are instantiated right.
 */
class RulesEntityController extends EntityAPIController {

  /**
   * Overriden.
   * @see DrupalDefaultEntityController#attachLoad()
   */
  protected function attachLoad(&$queried_entities, $revision_id = FALSE) {
    $schema = drupal_get_schema($this->entityInfo['base table']);
    // Retrieve stdClass records and turn them in rules objects stored in 'data'
    $entities = array();
    foreach ($queried_entities as $record) {
      $entity = $record->data;
      // Set the values of the other columns.
      foreach (array_keys($schema['fields'] + array('status' => TRUE)) as $field) {
        $entity->$field = $record->$field;
      }
      unset($entity->data, $entity->plugin);
      $entities[$entity->name] = $entity;
    }
    $queried_entities = $entities;
    parent::attachLoad($queried_entities, $revision_id);
  }

  /**
   * Override to add the providing module and make sure the name is set.
   * @see EntityAPIController#getDefaults($ids, $conditions)
   */
  protected function getDefaults($ids, $conditions = array()) {
    if (!isset($this->defaultEntities)) {
      $this->defaultEntities = array();
      $hook = $this->entityInfo['export']['default hook'];
      foreach (module_implements($hook) as $module) {
        $function = $module . '_' . $hook;
        foreach ((array)$function() as $name => $entity) {
          $entity->name = $name;
          $entity->module = $module;
          $entity->plugin = $entity->property('itemName');
          $entity->status |= ENTITY_IN_CODE;
          $this->defaultEntities[$name] = $entity;
        }
      }
      drupal_alter($hook, $this->defaultEntities);
    }
    $entities = $ids ? array_intersect_key($this->defaultEntities, array_flip($ids)) : $this->defaultEntities;
    return $this->applyConditions($entities, $conditions);
  }

  /**
   * Override to support having an event as condition.
   * @see EntityAPIController#applyConditions($entities, $conditions)
   * @see rules_query_rules_config_load_multiple_alter()
   */
  protected function applyConditions($entities, $conditions = array()) {
    if (isset($conditions['event'])) {
      foreach ($entities as $key => $entity) {
        if (!($entity instanceof RulesTriggerableInterface) || !in_array($conditions['event'], $entity->events())) {
          unset($entities[$key]);
        }
      }
      unset($conditions['event']);
    }
    return parent::applyConditions($entities, $conditions);
  }
}

/**
 * The RulesExtendable uses the rules cache to setup the defined extenders
 * and overrides automatically.
 */
abstract class RulesExtendable extends FacesExtendable {

  /**
   * Allows skipping setUp on wakeup for performance reasons. But be aware that
   * setUp is invoked automatically only if the plugin is executed.
   */
  protected $skipSetUp = FALSE;

  /**
   * The name of the info definitions associated with info about this class.
   * This would be defined abstract, if possible. Common rules hooks with class
   * info are e.g. plugin_info and data_info.
   */
  protected $hook;

  /**
   * The name of the item this class represents in the info hook.
   */
  protected $itemName;

  protected $cache, $itemInfo = array();


  public function __construct() {
    $this->setUp();
  }

  protected function setUp() {
    $this->cache = rules_get_cache();
    if (isset($this->cache[$this->hook][$this->itemName])) {
      $this->itemInfo = &$this->cache[$this->hook][$this->itemName];
    }
    // Set up the Faces Extenders
    if (!empty($this->itemInfo['faces_cache'])) {
      list($this->facesMethods, $this->facesIncludes, $this->faces) = $this->itemInfo['faces_cache'];
    }
  }

  /**
   * Sets skipSetup to the given value.
   */
  public function setSkipSetUp($value = TRUE) {
    $this->skipSetup = $value;
  }

  /**
   * Serialize only what's necessary. We don't save info about extended methods either.
   */
  function __sleep() {
    return array('skipSetUp');
  }

  /**
   * We have to setup again once we woke up. But we do only so, if we skipSetUp
   * isn't set. If activated, setUp will be invoked automatically once executed.
   */
  function __wakeup() {
    if (empty($this->skipSetUp)) {
      $this->setUp();
    }
  }

  /**
   * Force the object to be setUp, this executes setUp() if not done yet.
   */
  public function forceSetUp() {
    if (!isset($this->cache)) {
      $this->setUp();
    }
  }

  /**
   * Allows items to add something to the rules cache.
   */
  public function rebuildCache(&$itemInfo, &$cache) {
    // Speed up setting up items by caching the faces methods.
    if (!empty($itemInfo['extenders'])) {
      // Apply extenders and overrides.
      $itemInfo += array('overrides' => array());
      foreach ($itemInfo['extenders'] as $face => $data) {
        $data += array('file' => array());
        if (isset($data['class'])) {
          $this->extendByClass($face, $data['class'], $data['file']);
        }
        elseif (isset($data['methods'])) {
          $this->extend($face, $data['methods'], $data['file']);
        }
      }
      foreach ($itemInfo['overrides'] as $data) {
        $data += array('file' => array());
        $this->override($data['methods'], $data['file']);
      }
      $itemInfo['faces_cache'] = array($this->facesMethods, $this->facesIncludes, $this->faces);
      // We don't need that any more.
      unset($itemInfo['extenders'], $itemInfo['overrides']);
    }
  }

  /**
   * Returns whether the a RuleExtendable supports the given interface.
   *
   * @param $itemInfo
   *   The info about the item as specified in the hook.
   * @param $interface
   *   The interface to check for.
   * @return
   *   Whether it supports the given interface.
   */
  public static function itemFacesAs(&$itemInfo, $interface) {
    return in_array($interface, class_implements($itemInfo['class'])) || isset($itemInfo['faces_cache'][2][$interface]);
  }
}

/**
 * Base class for rules plugins.
 *
 * We cannot inherit from EntityDB at the same time, so we implement our own
 * entity related methods. Any CRUD related actions performed on contained
 * plugins are applied and the root element representing the configuration is
 * saved.
 */
abstract class RulesPlugin extends RulesExtendable {

  /**
   * If this is a configuration saved to the db, the id of it.
   */
  public $id = NULL;
  public $weight = 0;
  public $name = NULL;

  /**
   * An array of settings for this element.
   */
  public $settings = array();

  /**
   * Info about this element. Usage depends on the plugin.
   */
  protected $info = array();

  /**
   * The parent element, if any.
   * @var RulesContainerPlugin
   */
  protected $parent = NULL;

  protected $cache = NULL, $hook = 'plugin_info';

  /**
   *  The current evaluation state, if this plugin is currently executed.
   */
  protected $state = NULL;

  /**
   * Sets a new parent element.
   */
  public function setParent(RulesContainerPlugin $parent) {
    if ($this->parent == $parent) {
      return;
    }
    if (isset($this->parent) && ($key = array_search($this, $this->parent->children)) !== FALSE) {
      // Remove element from any previous parent.
      unset($this->parent->children[$key]);
    }
    // Make sure the interface matches the type of the container.
    if (($parent instanceof RulesActionContainer && $this instanceof RulesActionInterface) ||
       ($parent instanceof RulesConditionContainer && $this instanceof RulesConditionInterface)) {

      $this->parent = $parent;
      $parent->children[] = $this;
    }
    else {
      throw new RulesException('The given container is incompatible with this element.', array(), $this);
    }
  }

  /**
   * Gets the root element of the configuration.
   */
  public function root() {
    $element = $this;
    while (!empty($element->parent)) {
      $element = $element->parent;
    }
    return $element;
  }

  /**
   * Returns whether the element is the root of the configuration.
   */
  public function isRoot() {
    return empty($this->parent) || !empty($this->state);
  }

  /**
   * Returns the element's parent.
   */
  public function parentElement() {
    return $this->parent;
  }

  /**
   * Returns the depth of this element in the configuration.
   */
  public function depth() {
    $element = $this;
    $i = 0;
    while (!empty($element->parent)) {
      $element = $element->parent;
      $i++;
    }
    return $i;
  }

  /**
   * Execute the plugin.
   */
  public function execute() {}

  protected function evaluate(RulesState $state) {}

  protected static function compare(RulesPlugin $a, RulesPlugin $b) {
    if ($a->weight == $b->weight) {
      return 0;
    }
    return ($a->weight < $b->weight) ? -1 : 1;
  }

  /**
   * Returns info about parameters needed by the plugin. Note that not
   * necessarily all parameter need to be passed as argument when executing
   * as parameters might be already configured in the element's settings.
   *
   * @see self::argumentInfo()
   */
  public function parameterInfo() {
    return isset($this->info['parameter']) ? $this->info['parameter'] : array();
  }

  /**
   * Returns info about expected arguments when executing the plugin.
   */
  public function argumentInfo() {
    // We have to filter out parameters that are already configured.
    foreach ($this->parameterInfo() as $name => $info) {
      if (isset($this->settings[$name . ':select'])) {
        $parts = explode(':', $this->settings[$name . ':select'], 2);
        $vars[$parts[0]] = isset($parts[1]) ? array('type' => 'unknown') : $info;
      }
      elseif (!isset($this->settings[$name]) && empty($info['optional']) && $info['type'] != 'hidden') {
        $vars[$name] = $info;
      }
    }
    return isset($vars) ? $vars : array();
  }

  /**
   * Returns info about variables provided for later evaluated elements.
   */
  public function providesVariables() {
    if (isset($this->info['provides'])) {
      foreach ($this->info['provides'] as $name => $info) {
        $info['source name'] = $name;
        if (isset($this->settings[$name . ':var'])) {
          $name = $this->settings[$name . ':var'];
        }
        $info['label'] = isset($this->settings[$name . ':label']) ? $this->settings[$name . ':label'] : $info['label'];
        $provides[$name] = $info;
      }
    }
    return isset($provides) ? $provides : array();
  }

  /**
   * When converted to a string, just use the export format.
   */
  public function __toString() {
    return $this->isRoot() ? $this->export() : entity_var_json_export($this->export());
  }

  /**
   * Gets the arguments passed to the plugin.
   *
   * @return
   *   An array of passed arguments keyed by their names.
   */
  protected function getPassedArguments(array $args) {
    if (isset($args[0]) && count($args) == 1 && is_array($args[0]) && !empty($args[0]) && array_diff_key($args[0], $this->argumentInfo()) === array()) {
      // The caller passed an array of arguments, so let's use that
      return $args[0];
    }
    $vars = array();
    foreach (array_keys($this->argumentInfo()) as $i => $name) {
      if (isset($args[$i])) {
        $vars[$name] = $args[$i];
      }
    }
    return $vars;
  }

  /**
   * Gets variables to return once the configuration has been executed.
   */
  protected function returnVariables(RulesState $state, $result = NULL) {
    $var_info = $this->providesVariables();
    foreach ($var_info as $name => $info) {
      try {
        $vars[$name] = $this->getArgument($name, $info, $state);
      }
      catch (RulesException $e) {
        // Ignore not existing variables.
        $vars[$name] = NULL;
      }
    }
    return isset($vars) ? array_values(rules_unwrap_data($vars, $var_info)) : array();
  }

  /**
   * Sets up the execution state for the given arguments.
   *
   * The state will be passed around by copy to all evaluated rule elements,
   * so added 'variables' are only visible for elements in scope.
   */
  protected function setUpState(array $args) {
    $this->state = new RulesState($this);
    $args = $this->getPassedArguments($args);

    foreach ($this->setUpVariables() as $name => $info) {
      if (!isset($args[$name]) && empty($info['handler']) && empty($info['optional']) && $info['type'] != 'hidden') {
        throw new RulesException('Argument %name is missing.', array('%name' => $name), $this);
      }
      $args += array($name => NULL);
      $this->state->addVariable($name, $args[$name], $info);
    }
    return $this->state;
  }

  /**
   * Returns info about all variables that have to be setup in the state.
   */
  protected function setUpVariables() {
    return $this->argumentInfo();
  }

  /**
   * Returns info about variables available to be used as arguments for this
   * element.
   */
  public function availableVariables() {
    return !$this->isRoot() ? $this->parent->stateVariables($this) : array();
  }

  /**
   * Returns asserted additions to the available variable info. Any returned
   * info is merged into the variable info, in case the execution flow passes
   * the element.
   * E.g. this is used to assert the content type of a node if the condition
   * is met, such that the per node type properties are available.
   */
  protected function variableInfoAssertions() {
    return array();
  }

  /**
   * Get the name of this plugin instance. The returned name should identify
   * the code which drives this plugin.
   */
  public function getPluginName() {
    return $this->itemName;
  }

  /**
   * Returns an array of required modules.
   */
  public function dependencies() {
    $this->processSettings();
    $modules = isset($this->itemInfo['module']) && $this->itemInfo['module'] != 'rules' ? array($this->itemInfo['module'] => 1) : array();
    foreach ($this->parameterInfo() as $name => $info) {
      if (isset($this->settings[$name . ':process']) && $this->settings[$name . ':process'] instanceof RulesDataProcessor) {
        $modules += array_flip($this->settings[$name . ':process']->dependencies());
      }
    }
    return array_keys($modules);
  }

  /**
   * Whether the currently logged in user has access to configure (or to change)
   * this configuration.
   */
  public function access() {
    $this->processSettings();
    foreach ($this->parameterInfo() as $name => $info) {
      if (isset($this->settings[$name . ':select']) && $wrapper = $this->applyDataSelector($this->settings[$name . ':select'])) {
        if ($wrapper->access('view') === FALSE) {
          return FALSE;
        }
      }
      if (isset($this->settings[$name . ':process']) && $this->settings[$name . ':process'] instanceof RulesDataProcessor && !$this->settings[$name . ':process']->editAccess()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Processes the settings e.g. to prepare input evaluators.
   *
   * Usually settings get processed automatically, however if $this->settings
   * has been altered manually after element construction, it needs to be
   * invoked explicitly with $force set to TRUE.
   *
   * @param $access_check
   *   Whether only input evaluators accessible to the current user should be
   *   taken into account.
   */
  public function processSettings($force = FALSE, $access_check = FALSE) {
    // Process if not done yet.
    if ($force || !empty($this->settings['#_needs_processing'])) {
      $var_info = $this->availableVariables();
      foreach ($this->parameterInfo() as $name => $info) {
        // Prepare input evaluators.
        if (isset($this->settings[$name])) {
          $this->settings[$name . ':process'] = $this->settings[$name];
          RulesDataInputEvaluator::prepareSetting($this->settings[$name . ':process'], $info, $var_info, $access_check);
        }
        // Prepare data processors.
        elseif (isset($this->settings[$name . ':select']) && !empty($this->settings[$name . ':process'])) {
          RulesDataProcessor::prepareSetting($this->settings[$name . ':process'], $info, $var_info, $access_check);
        }
        // Clean up.
        if (empty($this->settings[$name . ':process'])) {
          unset($this->settings[$name . ':process']);
        }
      }
      unset($this->settings['#_needs_processing']);
    }
  }

  /**
   * Makes sure the plugin is configured right, e.g. all needed variables
   * are available in the element's scope and dependent modules are enabled.
   *
   * @return RulesPlugin
   */
  public function integrityCheck() {
    // First process the settings if not done yet.
    $this->processSettings();
    // Check dependencies.
    foreach ($this->dependencies() as $module) {
      if (!module_exists($module)) {
        throw new RulesException('Missing required module %name.', array('%name' => $module), $this);
      }
    }
    // Check the parameter settings.
    $this->checkParameterSettings();
    // Check variable names for provided variabels to be valid.
    foreach ($this->providesVariables() as $name => $info) {
      if (isset($this->settings[$name . ':var'])) {
        $this->checkVarName($this->settings[$name . ':var']);
      }
    }
    return $this;
  }

  protected function checkVarName($name) {
    if (!preg_match('/^[0-9a-zA-Z]*$/', $name)) {
      throw new RulesException('%plugin: The variable name @name contains not allowed characters.', array('%plugin' => $this->getPluginName(), '%name' => $name), $this);
    }
  }

  /**
   * Checks whether parameters are correctly configured.
   */
  protected function checkParameterSettings() {
    foreach ($this->parameterInfo() as $name => $info) {
      if (isset($info['restriction']) && $info['restriction'] == 'selector' && isset($this->settings[$name])) {
        throw new RulesException("%plugin: The parameter %name may only be configured using a selector.", array('%plugin' => $this->getPluginName(), '%name' => $name), array($this, 'parameter', $name));
      }
      elseif (isset($info['restriction']) && $info['restriction'] == 'input' && isset($this->settings[$name . ':select'])) {
        throw new RulesException("%plugin: The parameter %name may not be configured using a selector.", array('%plugin' => $this->getPluginName(), '%name' => $name), array($this, 'parameter', $name));
      }
      if ($arg_info = $this->getArgumentInfo($name)) {
        // If we have enough metadata, check whether the types match.
        if (!RulesData::typesMatch($arg_info, $info)) {
          throw new RulesException("%plugin: The data type of the configured argument does not match the parameter's %name requirement.", array('%plugin' => $this->getPluginName(), '%name' => $name), array($this, 'parameter', $name));
        }
      }
      elseif (!$this->isRoot() && !isset($this->settings[$name]) && empty($info['optional']) && $info['type'] != 'hidden') {
        throw new RulesException('%plugin: Missing configuration for parameter %name.', array('%plugin' => $this->getPluginName(), '%name' => $name), array($this, 'parameter', $name));
      }
      //TODO: Make sure used values are allowed. (key/value pairs + allowed values)
    }
  }

  /**
   * Returns the argument as configured in the element settings for the
   * parameter $name described with $info.
   *
   * @throws RulesException
   *   In case the argument cannot be retrieved an exception is thrown.
   */
  protected function getArgument($name, $info, RulesState $state) {
    if (isset($this->settings[$name . ':select']) && $state->varinfo($name)) {
      $arg = $state->get($this->settings[$name . ':select']);
    }
    elseif (isset($this->settings[$name . ':select'])) {
      $arg = $state->applyDataSelector($this->settings[$name . ':select']);
    }
    elseif (isset($this->settings[$name])) {
      $arg = $this->settings[$name];
      // We don't sanitize directly specified values.
      $skip_sanitize = TRUE;
    }
    elseif ($state->varinfo($name)) {
      $arg = $state->get($name);
    }
    elseif (empty($info['optional']) && $info['type'] != 'hidden') {
      throw new RulesException('Required parameter %name is missing.', array('%name' => $name), $this);
    }
    else {
      $arg = !empty($info['default value']) ? $info['default value'] : NULL;
      $skip_sanitize = TRUE;
    }
    // Apply any configured data processors.
    if (!empty($this->settings[$name . ':process'])) {
      // For processing, make sure the data is unwrapped now.
      $return = rules_unwrap_data(array($arg), array($info));
      return $this->settings[$name . ':process']->process($return[0], $info, $state, $this);
    }
    // Support passing already sanitized values.
    if ($info['type'] == 'text' && !isset($skip_sanitize) && !empty($info['sanitize']) && !($arg instanceof EntityMetadataWrapper)) {
      return check_plain((string)$arg);
    }
    return $arg;
  }

  /**
   * Apply the given data selector by using the info about available variables.
   * Thus it doesn't require an actual evaluation state.
   *
   * @param $selector
   *   The selector string, e.g. "node:author:mail".
   * @return EntityMetadataWrapper
   *   An empty wrapper for the given selector or FALSE if the selector couldn't
   *   be applied.
   */
  public function applyDataSelector($selector) {
    $parts = explode(':', $selector, 2);
    if (($vars = $this->availableVariables()) && isset($vars[$parts[0]]['type'])) {
      $data = NULL;
      $wrapper = rules_wrap_data($data, $vars[$parts[0]], TRUE);
      if (count($parts) > 1 && $wrapper instanceof EntityMetadataWrapper) {
        try {
          foreach (explode(':', $parts[1]) as $name) {
            $wrapper = $wrapper->get($name);
          }
        }
        // In case of an exception or we were unable to get a wrapper, return FALSE.
        catch (EntityMetadataWrapperException $e) {
          return FALSE;
        }
      }
    }
    return isset($wrapper) ? $wrapper : FALSE;
  }

  /**
   * Returns info about the configured argument.
   *
   * @return
   *   The determined type. If it's not known NULL is returned.
   */
  public function getArgumentInfo($name) {
    $vars = $this->availableVariables();
    if (isset($this->settings[$name . ':select']) && isset($vars[$this->settings[$name . ':select']])) {
      return $vars[$this->settings[$name . ':select']];
    }
    elseif (isset($this->settings[$name . ':select'])) {
      if ($wrapper = $this->applyDataSelector($this->settings[$name . ':select'])) {
        return $wrapper->info();
      }
      throw new RulesException('%plugin: Invalid data selector %selector specified.', array('%plugin' => $this->getPluginName(), '%selector' => $this->settings[$name . ':select']), array($this, 'parameter', $name, 'settings', $name . ':select'));
    }
    elseif (isset($this->settings[$name . ':type'])) {
      return array('type' => $this->settings[$name . ':type']);
    }
    elseif (!isset($this->settings[$name]) && isset($vars[$name])) {
      return $vars[$name];
    }
  }

  /**
   * Saves the configuration to the database, regardless whether this is invoked
   * on the rules configuration or a contained rule element.
   */
  public function save($name = NULL, $module = 'rules') {
    if (isset($this->parent)) {
      $this->parent->sortChildren();
      return $this->parent->save($name, $module);
    }
    else {
      $this->plugin = $this->itemName;
      $this->name = isset($name) ? $name : $this->name;
      $this->module = !isset($this->module) || $module != 'rules' ? $module : $this->module;
      $this->ensureNameExists();
      $this->data = $this;
      $return = entity_get_controller('rules_config')->save($this);
      unset($this->data);
      rules_clear_cache();
      return $return;
    }
  }

  /**
   * Ensure the configuration has a name. If not, generate one.
   */
  protected function ensureNameExists() {
    if (!isset($this->module)) {
      $this->module = 'rules';
    }
    if (!isset($this->name)) {
      // Find a unique name for this configuration.
      $this->name = $this->module . '_';
      for ($i = 0; $i < 8; $i++) {
        //Alphanumeric name generation
        $rnd = mt_rand(97, 122);
        $this->name .= chr($rnd);
      }
    }
  }

  public function __sleep() {
    $this->processSettings();
    $array = (isset($this->name)) ? array('name' => 'name') : array();
    // Keep the id always as we need it for the recursion prevention.
    return $array + drupal_map_assoc(array('skipSetUp', 'parent', 'id', 'weight', 'settings'));
  }

  /**
   * If invoked on a rules configuration it is deleted from database. If
   * invoked on a contained rule element, it's removed from the configuration.
   */
  public function delete() {
    if (isset($this->parent)) {
      foreach ($this->parent->children as $key => $child) {
        if ($child === $this) {
          unset($this->parent->children[$key]);
          break;
        }
      }
    }
    elseif (isset($this->id)) {
      entity_get_controller('rules_config')->delete(array($this->name));
      rules_clear_cache();
    }
  }

  public function internalIdentifier() {
    return isset($this->id) ? $this->id : NULL;
  }

  /**
   * Returns the config name.
   */
  public function identifier() {
    return isset($this->name) ? $this->name : NULL;
  }

  public function entityInfo() {
    return entity_get_info('rules_config');
  }

  public function entityType() {
    return 'rules_config';
  }

  /**
   * Remove circular object references so the PHP garbage collector does its
   * work.
   */
  public function destroy() {
    parent::destroy();
    $this->parent = NULL;
  }

  /**
   * Seamlessy invokes the method implemented via faces without having to think
   * about references.
   */
  public function form(&$form, &$form_state, array $options = array()) {
    $this->__call('form', array(&$form, &$form_state, $options));
  }

  public function form_validate($form, &$form_state) {
    $this->__call('form_validate', array($form, &$form_state));
  }

  public function form_submit($form, &$form_state) {
    $this->__call('form_submit', array($form, &$form_state));
  }

  /**
   * Returns the label of the element.
   */
  public function label() {
    if (!empty($this->label)) {
      return $this->label;
    }
    return isset($this->info['label']) ? $this->info['label'] : t('unlabeled');
  }

  /**
   * Returns the name of the element's plugin.
   */
  public function plugin() {
    return $this->itemName;
  }

  /**
   * Exports a rule configuration.
   *
   * @param $php
   *   Optional. Set to TRUE to format the export using PHP arrays. By default
   *   JSON is used.
   * @return
   *   The exported confiugration.
   */
  public function export($php = FALSE) {
    $export[strtoupper($this->plugin())] = $this->exportSettings();
    return $this->isRoot() ? $this->returnExport($export, $php) : $export;
  }

  protected function exportSettings() {
    $export = array();
    foreach ($this->parameterInfo() as $name => $info) {
      if (($return = $this->exportParameterSetting($name, $info)) !== NULL) {
        $export['USING'][$name] = $return;
      }
    }
    foreach ($this->providesVariables() as $name => $info) {
      $export['PROVIDE'][$info['source name']][$name] = $info['label'];
    }
    return $export;
  }

  protected function exportParameterSetting($name, $info) {
    if (isset($this->settings[$name]) && (!isset($info['default value']) || $this->settings[$name] != $info['default value'])) {
      return $this->settings[$name];
    }
    elseif (isset($this->settings[$name . ':select'])) {
      if (isset($this->settings[$name . ':process']) && $processor = $this->settings[$name . ':process']) {
        $export['select'] = $this->settings[$name . ':select'];
        $export += $processor instanceof RulesDataProcessor ? $processor->getChainSettings() : $processor;
        return $export;
      }
      // If there is no processor use a simple array to abbreviate this usual
      // case. In JSON this turns to a nice [selector] string.
      return array($this->settings[$name . ':select']);
    }
  }

  /**
   * Finalizies the configuration export by adding general attributes regarding
   * the configuration and returns it in the right format.
   */
  protected function returnExport($export, $php = FALSE) {
    $this->ensureNameExists();
    if (($label = $this->label()) && $label != t('unlabeled')) {
      $export_cfg[$this->name]['LABEL'] = $this->label();
    }
    $export_cfg[$this->name]['PLUGIN'] = $this->plugin();
    if ($modules = $this->dependencies()) {
      $export_cfg[$this->name]['REQUIRES'] = $modules;
    }
    $export_cfg[$this->name] += $export;
    return $php ? entity_var_export($export_cfg) : entity_var_json_export($export_cfg);
  }
}

/**
 * Defines a common base class for so called "Abstract Plugins" like actions.
 * Thus modules have to provide the concrete plugin implementation.
 */
abstract class RulesAbstractPlugin extends RulesPlugin {

  protected $elementName;
  protected $info = array('parameter' => array(), 'provides' => array());

  /**
   * @param $name
   *   The plugin implementation's name.
   * @param $info
   *   Further information provided about the plugin. Optional.
   * @throws RulesException
   *   If validation of the passed settings fails RulesExceptions are thrown.
   */
  function __construct($name = NULL, $settings = array()) {
    $this->elementName = $name;
    $this->settings = (array)$settings + array('#_needs_processing' => TRUE);
    $this->setUp();
  }

  protected function setUp() {
    parent::setUp();
    if (isset($this->cache[$this->itemName . '_info'][$this->elementName])) {
      $this->info = $this->cache[$this->itemName . '_info'][$this->elementName];
      // Add in per-plugin implementation callbacks if any.
      if (!empty($this->info['faces_cache'])) {
        foreach ($this->info['faces_cache'] as $face => $data) {
          list($methods, $file_names) = $data;
          foreach ($methods as $method => $callback) {
            $this->facesMethods[$method] = $callback;
          }
          foreach ((array)$file_names as $method => $name) {
            $this->facesIncludes[$method] = array('module' => $this->info['module'], 'name' => $name);
          }
        }
      }
    }
    elseif (function_exists($this->elementName)) {
      // We don't have any info, so just add the name as execution callback.
      $this->override(array('execute' => $this->elementName));
    }
    if (isset($this->settings['#info'])) {
      $this->info = rules_update_array($this->info, $this->settings['#info']);
    }
  }

  /**
   * Allows altering the element information depending on the configuration.
   *
   * @param $info
   *   An array of info, only containing the changes to the usual info array.
   */
  public function alterInfo($info) {
    $this->settings['#info'] = $info;
    // Apply the alter info to the element info by re-running setUp(). That way
    // we make sure to alter the un-modified element info.
    $this->setUp();
  }

  public function access() {
    $this->loadBasicInclude();
    if (!empty($this->info['access callback']) && !call_user_func($this->info['access callback'], $this->itemName, $this->getElementName())) {
      return FALSE;
    }
    return parent::access() && $this->__call('access');
  }

  public function integrityCheck() {
    // Do the usual integrity check first so the implementation's validation
    // handler can rely on that already.
    parent::integrityCheck();
    $this->validate();
    return $this;
  }

  public function processSettings($force = FALSE, $access_check = FALSE) {
    // Process if not done yet.
    if ($force || !empty($this->settings['#_needs_processing'])) {
      // First let the plugin implementation do processing, so data types of the
      // parameters are fixed when we process the settings.
      $this->process();
      parent::processSettings($force, $access_check);
    }
  }

  protected function variableInfoAssertions() {
    // Get the implementation's assertions and map them to the variable names.
    if ($assertions = $this->__call('assertions')) {
      $info = $this->parameterInfo();
      foreach ($assertions as $param_name => $data) {
        $name = isset($this->settings[$param_name . ':select']) ? $this->settings[$param_name . ':select'] : $param_name;
        $return[$name] = $data;
      }
      return $return;
    }
  }

  public function export($php = FALSE) {
    $export = $this->exportSettings();
    if (!$this->providesVariables()) {
      // Abbreviate the export making "USING" implicit.
      $export = $export['USING'];
    }
    $export = array($this->elementName => $export);
    return $this->isRoot() ? $this->returnExport($export, $php) : $export;
  }

  public function dependencies() {
    $modules = array_flip(parent::dependencies());
    $modules += (array) $this->__call('dependencies');
    return array_keys($modules + (isset($this->info['module']) ? array($this->info['module'] => 1) : array()));
  }

  public function execute() {
    $replacements = array('%label' => isset($this->info['label']) ? $this->info['label'] : $this->name, '%plugin' => $this->itemName);
    rules_log('Executing %plugin %label.', $replacements, RulesLog::INFO, TRUE);
    $this->forceSetUp();
    $this->processSettings();
    $args = func_get_args();
    // If there is no parameter info, just pass through the passed arguments.
    // That way we support executing actions without any info at all.
    if ($this->info['parameter']) {
      $state = $this->setUpState($args);
      module_invoke_all('rules_config_execute', $this);

      $result = $this->evaluate($state);
      $return = $this->returnVariables($state, $result);
    }
    else {
      $state = new RulesState($this);
      module_invoke_all('rules_config_execute', $this);
      $return = $this->executeCallback($args, $state);
    }
    $state->cleanUp($this);
    rules_log('Finished executing of %plugin %label.', $replacements, RulesLog::INFO, FALSE);
    return $return;
  }

  /**
   * Execute the configured execution callback and log that.
   */
  abstract protected function executeCallback(array $args, RulesState $state = NULL);


  protected function evaluate(RulesState $state) {
    $this->forceSetUp();
    $this->processSettings();
    try {
      // Get vars as needed for execute and call it.
      return $this->executeCallback($this->getExecutionArguments($state), $state);
    }
    catch (RulesException $e) {
      rules_log($e->msg, $e->args, $e->severity);
      rules_log('Unable to execute %name.', array('%name' => $this->getPluginName()));
    }
  }

  /**
   * Gets the right arguments for executing this.
   */
  protected function getExecutionArguments(RulesState $state) {
    foreach ($this->info['parameter'] as $name => $info) {
      $args[$name] = $this->getArgument($name, $info, $state);
    }
    // Append the settings and the execution state. Faces will append $this.
    $args['settings'] = $this->settings;
    $args['state'] = $state;
    // Make the variables for the arguments available in the state too.
    $state->currentArguments = $args;
    return !empty($this->info['named parameter']) ? array($args) : $args;
  }

  public function __sleep() {
    return parent::__sleep() + array('elementName' => 'elementName');
  }

  public function getPluginName() {
    return $this->itemName ." ". $this->elementName;
  }

  /**
   * Gets the name of the configured action or condition.
   */
  public function getElementName() {
    return $this->elementName;
  }

  /**
   * Add in the data provided by the info hooks to the cache.
   */
  public function rebuildCache(&$itemInfo, &$cache) {
    parent::rebuildCache($itemInfo, $cache);

    // Include all declared files so we can find all implementations.
    self::includeFiles();

    // Get the plugin's own info data.
    $cache[$this->itemName .'_info'] = rules_fetch_data($this->itemName .'_info');
    foreach ($cache[$this->itemName .'_info'] as $name => &$info) {
      $info += array(
        'parameter' => isset($info['arguments']) ? $info['arguments'] : array(),
        'provides' => isset($info['new variables']) ? $info['new variables'] : array(),
        'base' => $name,
        'callbacks' => array(),
      );
      unset($info['arguments'], $info['new variables']);
      $info['callbacks'] += array('execute' => $info['base']);

      // Build up the per plugin implementation faces cache.
      foreach ($this->faces as $interface) {
        $methods = $file_names = array();
        $includes = self::getIncludeFiles($info['module']);

        foreach (get_class_methods($interface) as $method) {
          if (isset($info['callbacks'][$method]) && ($function = $info['callbacks'][$method])) {
            $methods[$method][0] = $function;
            $file_names[$method] = $this->getFileName($function, $includes);
          }
          elseif (function_exists($function = $info['base'] . '_' . $method)) {
            $methods[$method][0] = $function;
            $file_names[$method] = $this->getFileName($function, $includes);
          }
        }
        // Cache only the plugin implementation specific callbacks.
        $info['faces_cache'][$interface] = array($methods, array_filter($file_names));
      }
      // Filter out interfaces with no overriden methods.
      $info['faces_cache'] = rules_filter_array($info['faces_cache'], 0, TRUE);
      // We don't need that any more.
      unset($info['callbacks'], $info['base']);
    }
  }

  /**
   * Makes sure the providing modules' .rules.inc file is included as diverse
   * callbacks may reside in that file.
   */
  protected function loadBasicInclude() {
    static $included = array();
    $module = $this->info['module'];

    if (!isset($included[$module])) {
      module_load_include('inc', $module, $module . '.rules');
      $included[$module] = TRUE;
    }
  }

  /**
   * Make sure all supported destinations are included.
   */
  protected static function includeFiles() {
    static $included;

    if (!isset($included)) {
      foreach (module_implements('rules_file_info') as $module) {
        // rules.inc are already included thanks to the rules_hook_info() group.
        foreach (self::getIncludeFiles($module, FALSE) as $name) {
          module_load_include('inc', $module, $name);
        }
      }
      $included = TRUE;
    }
  }

  /**
   * Returns all include files for a module. If $all is set to FALSE the
   * $module.rules.inc file isn't added.
   */
  protected static function getIncludeFiles($module, $all = TRUE) {
    $files = (array)module_invoke($module, 'rules_file_info');
    // Automatically add "$module.rules_forms.inc" and "$module.rules.inc".
    $files[] = $module . '.rules_forms';
    if ($all) {
      $files[] = $module . '.rules';
    }
    return $files;
  }

  protected function getFileName($function, $includes) {
    $reflector = new ReflectionFunction($function);
    // On windows the path contains backslashes instead of slashes, fix that.
    $file = str_replace('\\', '/', $reflector->getFileName());
    foreach ($includes as $include) {
      $pos = strpos($file, $include . '.inc');
      // Test whether the file ends with the given filename.inc.
      if ($pos !== FALSE && strlen($file) - $pos == strlen($include) + 4) {
        return $include;
      }
    }
  }
}

/**
 * Interface for objects that can be used as action.
 */
interface RulesActionInterface {
  /**
   * @return As specified.
   */
  public function execute();
}

/**
 * Interface for objects that can be used as condition.
 */
interface RulesConditionInterface {

  /**
   * @return Boolean.
   */
  public function execute();

  /**
   * Negate the result.
   */
  public function negate($negate = TRUE);

  /**
   * Returns whether the element is configured to negate the result.
   */
  public function isNegated();
}

interface RulesTriggerableInterface {

  /**
   * Returns a reference on the array of event names associated with this
   * object.
   */
  public function &events();

  /**
   * Adds the specified event.
   *
   * @return RulesTriggerableInterface
   */
  public function event($event);
}

/**
 * Provides the interface used for implementing an abstract plugin by using
 * the Faces extension mechanism.
 */
interface RulesPluginImplInterface {

  /**
   * Execute the action or condition making use of the parameters as specified.
   */
  public function execute();

  /**
   * Validates $settings independent from a form submission.
   *
   * @throws RulesException
   *   In case of validation errors, RulesExceptions are thrown.
   */
  public function validate();

  /**
   * Processes $settings independent from a form submission. Only successfully
   * validated settings are processed, so it can be also used to prepare
   * execution dependent on the settings.
   */
  public function process();

  /**
   * Checks whether the user has access to configure this element or rule
   * configuration.
   */
  public function access();

  /**
   * Returns an array of required modules.
   */
  public function dependencies();

  /**
   * Alter the generated configuration form of the element.
   *
   * Validation and processing of the settings should be untied from the form
   * and implemented in validate() and process() wherever it makes sense.
   * For the remaining cases where form tied validation and processing is needed
   * make use of the form API #element_validate and #value_callback properties.
   */
  public function form_alter(&$form, $form_state, $options);


  /**
   * Optionally returns an array of info assertions for the specified
   * parameters. This allows conditions to assert additional metadata, such as
   * info about the fields of a bundle.
   *
   * @see RulesPlugin::variableInfoAssertions()
   */
  public function assertions();

}

/**
 * Class providing default implementations of the methods of the RulesPluginImplInterface.
 *
 * If a plugin implementation does not provide a function for a method, the
 * default method of this class will be invoked.
 *
 * @see RulesPluginImplInterface
 * @see RulesAbstractPlugin
 */
class RulesAbstractPluginDefaults extends FacesExtender implements RulesPluginImplInterface {

  public function execute() {
    throw new RulesException($this->object->getPluginName() .": Execution implementation is missing.");
  }

  /**
   * Implements RulesPluginImplInterface.
   */
  public function access() {
    return TRUE;
  }

  public function validate() {}
  public function process() {}
  public function dependencies() {}
  public function form_alter(&$form, $form_state, $options) {}
  public function assertions() {}
}


/**
 * A RecursiveIterator for rule elements.
 */
class RulesRecursiveElementIterator extends ArrayIterator implements RecursiveIterator {

   public function getChildren() {
     return $this->current()->getIterator();
   }

   public function hasChildren() {
      return $this->current() instanceof IteratorAggregate;
   }
}

/**
 * Base class for ContainerPlugins like Rules, Logical Operations or Loops.
 */
abstract class RulesContainerPlugin extends RulesPlugin implements IteratorAggregate {

  protected $children = array();

  public function __construct($variables = array()) {
    $this->setUp();
    if (!empty($variables)) {
      $this->info['variables'] = $variables;
    }
  }

  /**
   * Allow access to the children through the iterator.
   *
   * @return RulesRecursiveElementIterator
   */
  public function getIterator() {
    return new RulesRecursiveElementIterator($this->children);
  }

  /**
   * @return RulesContainerPlugin
   */
  public function integrityCheck() {
    if (!empty($this->info['variables']) && !$this->isRoot()) {
      throw new RulesException('%plugin: Specifying state variables is not possible for child elements.', array('%plugin' => $this->getPluginName()), $this);
    }
    parent::integrityCheck();
    foreach ($this->children as $child) {
      $child->integrityCheck();
    }
    return $this;
  }

  public function dependencies() {
    $modules = array();
    foreach ($this->children as $child) {
      $modules += array_flip($child->dependencies());
    }
    return array_keys($modules);
  }

  public function argumentInfo() {
    if (isset($this->info['variables'])) {
      return parent::argumentInfo() + rules_filter_array($this->info['variables'], 'handler', FALSE);
    }
    return parent::argumentInfo();
  }

  public function availableVariables() {
    if ($this->isRoot()) {
      return isset($this->info['variables']) ? $this->info['variables'] : array();
    }
    return parent::availableVariables();
  }

  /**
   * Returns info about variables available in the evaluation state for any
   * children elements or if given for a special child element.
   */
  protected function stateVariables($element = NULL) {
    $vars = $this->availableVariables();
    if (isset($element)) {
      // Add in variables provided by siblings executed before the element.
      foreach ($this->children as $child) {
        if ($child === $element) {
          break;
        }
        $vars += $child->providesVariables();
        // Take variable info assertions into account.
        if ($assertions = $child->variableInfoAssertions()) {
          $vars = RulesData::addMetadataAssertions($vars, $assertions);
        }
      }
    }
    return $vars;
  }

  protected function variableInfoAssertions() {
    $assertions = array();
    foreach ($this->children as $child) {
      if ($add = $child->variableInfoAssertions()) {
        $assertions = rules_update_array($assertions, $add);
      }
    }
    return $assertions;
  }

  protected function setUpVariables() {
    return isset($this->info['variables']) ? parent::argumentInfo() + $this->info['variables'] : $this->argumentInfo();
  }

  /**
   * Sets skipSetup to the given value for this plugin and all its children.
   */
  public function setSkipSetUp($value = TRUE) {
    foreach ($this->children as $child) {
      $child->setSkipSetUp($value);
    }
    $this->skipSetup = $value;
  }

  /**
   * Condition containers just return a boolean while action containers return
   * the configured provided variables as an array of variables.
   */
  public function execute() {
    $replacements = array('%label' => isset($this->info['label']) ? $this->info['label'] : $this->name, '%plugin' => $this->itemName);
    rules_log('Executing %plugin %label.', $replacements, RulesLog::INFO, TRUE);
    $this->processSettings();
    $state = $this->setUpState(func_get_args());
    module_invoke_all('rules_config_execute', $this);

    $result = $this->evaluate($state);
    $return = $this->returnVariables($state, $result);
    $state->cleanUp($this);
    rules_log('Finished executing of %plugin %label.', $replacements, RulesLog::INFO, FALSE);
    return $return;
  }

  public function access() {
    foreach ($this->children as $key => $child) {
      if (!$child->access()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  public function destroy() {
    foreach ($this->children as $key => $child) {
      $child->destroy();
    }
    parent::destroy();
  }

  /**
   * By default we do a deep clone.
   */
  public function __clone() {
    foreach ($this->children as $key => $child) {
      $this->children[$key] = clone $child;
    }
  }

  /**
   * Override delete to keep the children alive, if possible.
   */
  public function delete() {
    if (isset($this->parent)) {
      foreach ($this->children as $child) {
        $child->setParent($this->parent);
      }
    }
    parent::delete();
  }

  public function __sleep() {
    return parent::__sleep() + array('children' => 'children', 'info' => 'info');
  }

  /**
   * Sorts all child elements by their weight.
   *
   * @param $deep
   *   If enabled a deep sort is performed, thus the whole element tree below
   *   this element is sorted.
   */
  public function sortChildren($deep = FALSE) {
    usort($this->children, array('RulesPlugin', 'compare'));
    if ($deep) {
      foreach (new ParentIterator($this->getIterator()) as $child) {
        $child->sortChildren(TRUE);
      }
    }
  }

  protected function exportChildren($key = NULL) {
    $key = isset($key) ? $key : strtoupper($this->plugin());
    $export[$key] = array();
    foreach ($this->children as $child) {
      $export[$key][] = $child->export();
    }
    return $export;
  }

  public function export($php = FALSE) {
    $export = array();
    if (isset($this->info['variables'])) {
      $export['USES VARIABLES'] = $this->info['variables'];
    }
    $export += $this->exportChildren();
    return $this->isRoot() ? $this->returnExport($export, $php) : $export;
  }
}

/**
 * Base class for all action containers.
 */
abstract class RulesActionContainer extends RulesContainerPlugin implements RulesActionInterface {

  public function __construct($variables = array(), $providesVars = array()) {
    $this->setUp();
    $this->info['variables'] = $variables;
    $this->info['provides'] = $providesVars;
    $this->info = array_filter($this->info);
  }

  /**
   * Add an action. Pass either an instance of the RulesActionInterface
   * or the arguments as needed by rules_action().
   *
   * @return RulesActionContainer
   *   Returns $this to support chained usage.
   */
  public function action($name, $settings = array()) {
    $action = (is_object($name) && $name instanceof RulesActionInterface) ? $name : rules_action($name, $settings);
    $action->setParent($this);
    return $this;
  }

  /**
   * As default we add provided variables to the parent's scope.
   */
  protected function evaluate(RulesState $state) {
    foreach ($this->children as $action) {
      $action->evaluate($state);
    }
  }
}

/**
 * Base class for all condition containers.
 */
abstract class RulesConditionContainer extends RulesContainerPlugin implements RulesConditionInterface {

  protected $negate = FALSE;

  /**
   * Add a condition. Pass either an instance of the RulesConditionInterface
   * or the arguments as needed by rules_condition().
   *
   * @return RulesConditionContainer
   *   Returns $this to support chained usage.
   */
  public function condition($name, $settings = array()) {
    $condition = (is_object($name) && $name instanceof RulesConditionInterface) ? $name : rules_condition($name, $settings);
    $condition->setParent($this);
    return $this;
  }

  /**
   * Negate this condition.
   *
   * @return RulesConditionContainer
   */
  public function negate($negate = TRUE) {
    $this->negate = (bool) $negate;
    return $this;
  }

  public function isNegated() {
    return $this->negate;
  }

  public function __sleep() {
    return parent::__sleep() + array('negate' => 'negate');
  }

  /**
   * Just return the condition container's result.
   */
  protected function returnVariables(RulesState $state, $result = NULL) {
    return $result;
  }

  protected function exportChildren($key = NULL) {
    $not = $this->negate ? 'NOT ' : '';
    $key = isset($key) ? $key : $not . strtoupper($this->plugin());
    $export[$key] = array();
    foreach ($this->children as $child) {
      $export[$key][] = $child->export(TRUE);
    }
    return $export;
  }

  /**
   * Overridden to exclude variable assertions of negated conditions.
   */
  protected function stateVariables($element = NULL) {
    $vars = $this->availableVariables();
    if (isset($element)) {
      // Add in variables provided by siblings executed before the element.
      foreach ($this->children as $child) {
        if ($child === $element) {
          break;
        }
        $vars += $child->providesVariables();
        // Take variable info assertions into account.
        if (!$this->negate && !$child->isNegated() && ($assertions = $child->variableInfoAssertions())) {
          $vars = RulesData::addMetadataAssertions($vars, $assertions);
        }
      }
    }
    return $vars;
  }
}

/**
 * The rules default logging class.
 */
class RulesLog {

  const INFO  = 1;
  const WARN  = 2;
  const ERROR = 3;

  static protected $logger;

  /**
   * @return RulesLog
   *   Returns the rules logger instance.
   */
  static function logger() {
    if (!isset(self::$logger)) {
      $class = __CLASS__;
      self::$logger = new $class(variable_get('rules_log_level', self::INFO));
    }
    return self::$logger;
  }

  protected $log = array();
  protected $logLevel, $line = 0;

  /**
   * This is a singleton.
   */
  protected function __construct($logLevel = self::WARN) {
    $this->logLevel = $logLevel;
  }

  public function __clone() {
    throw new Exception("Cannot clone the logger.");
  }

  function log($msg, $args = array(), $logLevel = self::INFO, $scope = NULL) {
    if ($logLevel >= $this->logLevel) {
      $this->log[] = array($msg, $args, $logLevel, microtime(TRUE), $scope);
    }
  }

  /**
   * Checks the log and throws an exception if there were any problems.
   */
  function checkLog($logLevel = self::WARN) {
    foreach ($this->log as $entry) {
      if ($entry[2] >= $logLevel) {
        throw new Exception($this->render());
      }
    }
  }

  function get() {
    return $this->log;
  }

  /**
   * Renders the whole log.
   */
  function render() {
    $line = 0;
    $output = array();
    while (isset($this->log[$line])) {
      $output[] = $this->renderHelper($line);
      $line++;
    }
    return implode('', $output);
  }

  /**
   * Renders the log of one event invocation.
   */
  protected function renderHelper(&$line = 0) {
    $startTime = isset($this->log[$line][3]) ? $this->log[$line][3] : 0;
    $output = array();
    while ($line < count($this->log)) {
      if ($output && !empty($this->log[$line][4])) {
        // The next entry stems from another evaluated set, add in its log messages here
        $output[] = $this->renderHelper($line);
      }
      else {
        $formatted_diff = round(($this->log[$line][3] - $startTime) * 1000, 3) .' ms';
        $msg = $formatted_diff .' '. t($this->log[$line][0], $this->log[$line][1]);
        if ($this->log[$line][2] >= RulesLog::WARN) {
          $msg = '<strong>'. $msg .'</strong>';
        }
        $output[] = $msg;

        if (isset($this->log[$line][4]) && !$this->log[$line][4]) {
          // This was the last log entry of this set
          return theme('item_list', array('items' => $output));
        }
      }
      $line++;
    }
    return theme('item_list', array('items' => $output));
  }

  function clear() {
    $this->log = array();
  }

}

class RulesException extends Exception {

  public $msg, $args, $severity, $element, $keys = array();

  /**
   * @param $msg
   *   The exception message containing placeholder as t().
   * @param $args
   *   Replacement arguments such as for t().
   * @param $element
   *   The element of a configuration causing the exception or an array
   *   consisting of the element and keys specifying a setting value causing
   *   the exception.
   * @param $severity
   *   The RulesLog severity. Defaults to RulesLog::WARN.
   */
  function __construct($msg, $args = array(), $element = NULL, $severity = RulesLog::WARN) {
    $this->element = is_array($element) ? array_shift($element) : $element;
    $this->keys = is_array($element) ? $element : array();
    $this->msg = $msg;
    $this->args = $args;
    $this->severity = $severity;
    unset($this->message);
  }

  /**
   * The getMessage() method cannot be overriden, so this is the only
   * possibility to override its output.
   */
  public function __get($name) {
    if ($name == 'message') {
      return $this->__toString();
    }
    return parent::$name;
  }

  public function __toString() {
    return t($this->msg, $this->args);
  }
}
