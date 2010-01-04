<?php
// $Id$

/**
 * @file
 * Rules base classes and interfaces needed for any rule evaluation.
 */

//TODO: add schedule routine that works like executing, but takes a $date as first argument.

/**
 * Make sure loaded rule configs are instantiated right.
 */
class RulesEntityController extends EntityCRUDController {

  /**
   * Overriden.
   * @see DrupalDefaultEntityController#attachLoad()
   */
  protected function attachLoad(&$queried_entities) {
    $schema = drupal_get_schema($this->entityInfo['base table']);
    // Retrieve stdClass records and turn them in rules objects stored in 'data'
    $entities = array();
    foreach ($queried_entities as $record) {
      $entity = $record->data;
      // Set the values of the other columns.
      foreach (array_keys($schema['fields']) as $field) {
        $entity->$field = $record->$field;
      }
      unset($entity->data, $entity->plugin);
      $entities[$entity->id] = $entity;
    }
    $queried_entities = $entities;
    parent::attachLoad($queried_entities);
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
  public static function itemFacesAs($itemInfo, $interface) {
    $reflection = new ReflectionClass($itemInfo['class']);
    return ($reflection->implementsInterface($interface) || isset($itemInfo['faces_cache'][2][$interface]));
  }
}

/**
 * Base class for rules plugins.
 *
 * We implement the EntityCRUDInterface ourself as we cannot inherit from
 * EntityDB too. Any CRUD related actions performed on contained plugins are
 * applied and the real db-entity is saved.
 */
abstract class RulesPlugin extends RulesExtendable implements EntityCRUDInterface {

  /**
   * If this is a configuration saved to the db, the id of it.
   */
  public $id = NULL;
  public $weight = 0;
  public $name = 'unnamed';

  /**
   * The parent element, if any.
   * @var RulesContainerPlugin
   */
  protected $parent = NULL;

  protected $cache = NULL, $hook = 'plugin_info';

  /**
   *  The current execution state, if this plugin is currently executed.
   */
  protected $state = NULL;

  protected function setParent(RulesContainerPlugin $parent) {
    if (!isset($this->parent)) {
      $this->parent = $parent;
      if ($parent->stateVariables()) {
        // If variables are available, check integrity now.
        $this->integrityCheck();
      }
    }
  }

  /**
   * Execute the plugin.
   */
  public function execute() {}

  protected function evaluate(array &$state) {}


  protected static function compare(RulesPlugin $a, RulesPlugin $b) {
    if ($a->weight == $b->weight) {
      return 0;
    }
    return ($a->weight < $b->weight) ? -1 : 1;
  }

  /**
   * Returns info about parameters needed for executing the plugin.
   */
  public function parameterInfo() {
    return array();
  }

  /**
   * Returns info about variables provided for later evaluated elements.
   */
  public function providesVariables() {
    return array();
  }

  /**
   * Provide the readable export format.
   */
  public function __toString() {
    //TODO make abstract and implement
    return $this->getPluginName();
  }

  /**
   * Gets the arguments passed to the plugin.
   *
   * @return
   *   An array of passed arguments keyed by their names.
   */
  protected function getPassedArguments(array $args) {
    if (isset($args[0]) && count($args) == 1 && is_array($args[0]) && !empty($args[0]) && array_diff_key($args[0], $this->parameterInfo()) === array()) {
      // The caller passed an array of arguments, so let's use that
      return $args[0];
    }
    $vars = array();
    foreach (array_keys($this->parameterInfo()) as $i => $name) {
      if (isset($args[$i])) {
        $vars[$name] = $args[$i];
      }
    }
    return $vars;
  }

  /**
   * Sets up the execution state for the given arguments.
   *
   * The state will be passed around by copy to all evaluated rule elements,
   * so added 'variables' are only visible for elements in scope.
   */
  protected function setUpState(array $args) {
    $this->state = array(
      'variables' => array(),
      'all variables' => array(),
      'executed' => $this,

      // Maps metadata wrappers to variables, needed to save changes.
      'wrapper map' => new RulesWrapperMap(),

      // Keep the ids of blocked rules due to recursion prevention. Note that
      // we use an ArrayObject to ensure a unique array per execution state.
      'blocked' => isset($this->state) ? $this->state['blocked'] : new ArrayObject(),

      // If this is a recursive execution, save the previous state for later.
      'stack' => isset($this->state) ? $this->state : NULL,
    );
    $args = $this->getPassedArguments($args);
    foreach ($this->stateVariables() as $name => $info) {
      if (!isset($args[$name]) && empty($info['handler']) && empty($info['optional'])) {
        throw new RulesException('Argument ' . check_plain($name) . ' is missing.');
      }
      $args += array($name => NULL);
      $this->addVariable($name, $args[$name], $info, $this->state);
    }
    return $this->state;
  }

  /**
   * Run cleanup operations after execution. Takes care of saving changed vars
   * and makes sure the garbage collector can destruct them.
   */
  protected function cleanUp() {
    if (isset($this->state) && $this->state['executed'] === $this) {
      // Make changes permanent.
      $vars = array_merge($this->state['wrapper map']->variables(), $this->state['all variables']);
      foreach ($vars as $var) {
        $var->saveChanges(TRUE, $this->state)->destroy();
      }
    }
    // For recursive invocations pop the state from the stack, else set NULL.
    $this->state = $this->state['stack'];
  }

  /**
   * Returns info about variables available to be used as arguments for this
   * element.
   */
  public function availableVariables() {
    $vars = array();
    if (isset($this->parent) && (empty($this->state) || $this->state['executed'] !== $this)) {
      $vars += $this->parent->stateVariables();
      // Add in variables provided by siblings executed before us.
      foreach ($this->parent->children as $child) {
        if ($child === $this) {
          break;
        }
        $vars += $child->providesVariables();
      }
    }
    return $vars;
  }

  /**
   * Returns info about variables available in the execution state for this
   * or any children elements.
   */
  protected function stateVariables() {
    if (!isset($this->parent) || (!empty($this->state) && $this->state['executed'] === $this)) {
      return $this->parameterInfo();
    }
    return $this->availableVariables();
  }

  /**
   * Adds the given variable to the given execution state.
   */
  protected function addVariable($name, &$data, $info, &$state) {
    if ($variable = rules_wrap_data($data, $info)) {
      $variable->setVariableName($name);
      // In the outer most scoped execution state we store references to all
      // variables added during the whole evaluation. So we can access them
      // after execution and cleanup.
      $state['executed']->state['all variables'][] = $variable;
    }
    else {
      // If there is no wrapper class, just pass through the data.
      $variable = $data;
    }
    $state['variables'][$name] = $variable;
  }

  /**
   * Get the name of this plugin instance. The returned name should identify
   * the code which drives this plugin.
   */
  public function getPluginName() {
    return $this->itemName;
  }

  /**
   * Get required modules.
   */
  public function dependencies() {
    return array();
  }

  /**
   * Makes sure the plugin is configured right, e.g. all needed variables
   * are available in the plugin's scope and dependent modules are enabled.
   * //TODO: add integrity check for dependencies.
   */
  public function integrityCheck() {
    // Check whether any identified needed parameters match the *specified* parameterInfo.
    if (isset($this->parent)) {
      $vars = $this->availableVariables();
      foreach ($this->parameterInfo() as $name => $info) {
        if (!isset($vars[$name])) {
          throw new RulesException('%plugin: There is no variable for the parameter %name available.', array('%plugin' => $this->getPluginName(), '%name' => $name));
        }
        elseif (!RulesDataWrapper::typesMatch($vars[$name]['type'], $info['type'])) {
          throw new RulesException("%plugin: The data type of the variable %name does not match the parameter's requirement.", array('%plugin' => $this->getPluginName(), '%name' => $name));
        }
      }
    }
  }

  /**
   * Implements the EntityCRUDInterface.
   *
   * Saves the configuration to the database, regardless whether this is invoked
   * on the rules configuration or a contained rule element.
   */
  public function save($name = 'unnamed', $module = 'rules') {
    if (isset($this->parent)) {
      $this->parent->sortChildren();
      return $this->parent->save($name, $module);
    }
    else {
      $this->name = $name;
      $this->module = $module;
      $this->plugin = $this->itemName;
      $this->data = $this;
      $this->invoke('presave');
      if (isset($this->id)) {
        $return = drupal_write_record('rules_config', $this, 'id');
        $this->invoke('update');
      }
      else {
        $return = drupal_write_record('rules_config', $this);
        $this->invoke('insert');
      }
      unset($this->data);
      rules_clear_cache();
      return $return;
    }
  }

  function __sleep() {
    $array = ($this->name != 'unnamed') ? array('name' => 'name') : array();
    // Keep the id always as we need it for the recursion prevention.
    return $array + drupal_map_assoc(array('skipSetUp', 'parent', 'id', 'weight'));
  }

  /**
   * Implements the EntityCRUDInterface.
   *
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
      db_delete('rules_config')
        ->condition('id', $this->id)
        ->execute();
      $this->invoke('delete');
      entity_get_controller('rules_config')->resetCache();
      rules_clear_cache();
    }
  }

  /**
   * Implement EntityCRUDInterface.
   */
  public function identifier() {
    return isset($this->id) ? $this->id : NULL;
  }

  /**
   * Implement EntityCRUDInterface.
   */
  public function entityInfo() {
    return entity_get_info('rules_config');
  }

  /**
   * Implement EntityCRUDInterface.
   */
  public function entityType() {
    return 'rules_config';
  }

  /**
   * Implement EntityCRUDInterface.
   */
  public function invoke($hook) {
    $info = $this->entityInfo();
    if (!empty($info['fieldable']) && function_exists($function = 'field_attach_' . $hook)) {
      $function('rules_config', $this);
    }
    module_invoke_all('rules_config_' . $hook, $this);
  }
}


/**
 * Defines a common base class for so called "Abstract Plugins" like actions.
 * Thus modules have to provide the concrete plugin implementation.
 */
abstract class RulesAbstractPlugin extends RulesPlugin {

  protected $elementName;
  protected $info = array('parameter' => array(), 'provides' => array());
  public $settings = array();

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
    $this->settings = $settings + array('#_needs_processing' => TRUE);
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

  public function integrityCheck() {
    parent::integrityCheck();
    // Also validate and process the concrete plugin's settings.
    $this->processSettings();
  }

  protected function processSettings() {
    // Process if not done yet.
    if (!empty($this->settings['#_needs_processing'])) {
      $this->validate();
      $this->process();
      unset($this->settings['#_needs_processing']);
    }
  }

  public function dependencies() {
    //TODO: add used input evaluator modules
    return isset($this->info['module']) ? array($this->info['module']) : array();
  }

  public function parameterInfo() {
    $vars = array();
    // Assemble info about variables that are needed for executing.
    foreach ($this->info['parameter'] as $name => $info) {
      if (isset($this->settings[$name . ':select'])) {
        $parts = explode(':', $this->settings[$name . ':select'], 2);
        $vars[$parts[0]] = isset($parts[1]) ? array('type' => 'unknown') : $info;
      }
      elseif (!isset($this->settings[$name]) && empty($info['optional'])) {
        $vars[$name] = $info;
      }
    }
    return $vars;
  }

  /**
   * Returns info about provided variables by this plugin.
   */
  public function providesVariables() {
    $provides = array();
    foreach ($this->info['provides'] as $name => $info) {
      if (isset($this->settings[$name . ':var'])) {
        $name = $this->settings[$name . ':var'];
      }
      $provides[$name] = $info;
    }
    return $provides;
  }

  public function execute() {
    $replacements = array('%label' => isset($this->info['label']) ? $this->info['label'] : $this->name, '%plugin' => $this->itemName);
    rules_log('Executing %plugin %label.', $replacements, RulesLog::INFO, TRUE);
    $state = array();
    $this->forceSetUp();
    $this->processSettings();
    $args = func_get_args();
    // If there is no parameter info, just pass through the passed arguments.
    // That way we support executing actions without any info at all.
    if ($this->info['parameter']) {
      $state = $this->setUpState($args);
      $args = $this->getExecutionArguments($state);
    }
    $return = $this->executeCallback($args, $state);
    $this->cleanUp();
    rules_log('Finished executing of %plugin %label.', $replacements, RulesLog::INFO, FALSE);
    return $return;
  }

  /**
   * Execute the configured execution callback and log that.
   */
  abstract protected function executeCallback(array $args, array &$state = array());


  protected function evaluate(array &$state) {
    $this->forceSetUp();
    try {
      // Get vars as needed for execute and call it.
      return $this->executeCallback($this->getExecutionArguments($state), $state);
    }
    catch (RulesException $e) {
      rules_log($e->msg, $e->args, RulesLog::WARN);
      rules_log('Unable to execute %name.', array('%name' => $this->getPluginName()));
    }
  }

  /**
   * Gets the right arguments for executing this.
   */
  protected function getExecutionArguments(array $state) {
    $vars = &$state['variables'];
    $args = array();
    foreach ($this->info['parameter'] as $name => $info) {
      if (isset($this->settings[$name . ':select']) && isset($vars[$this->settings[$name . ':select']])) {
        $args[$name] = $vars[$this->settings[$name . ':select']];
      }
      elseif (isset($this->settings[$name . ':select'])) {
        $args[$name] = $this->getSelectedVariable($this->settings[$name . ':select'], $info, $state);
      }
      elseif (isset($this->settings[$name])) {
        $args[$name] = $this->settings[$name];
      }
      elseif (isset($vars[$name])) {
        $args[$name] = $vars[$name];
      }
      elseif (empty($info['optional'])) {
        throw new RulesException('Required parameter ' . check_plain($name) . ' is missing.');
      }
      else {
        $args[$name] = !empty($info['default value']) ? $info['default value'] : NULL;
      }
      // Support passing already sanitized values.
      if ($info['type'] == 'text' && !empty($info['sanitized'])) {
        //TODO: support a method for getting sanitized values of text wrappers,
        // so tokens can make use of it.
        $args[$name] = check_plain((string)$args[$name]);
      }
    }
    // Append the settings, $this and the execution state.
    $args[] = $this->settings;
    $args[] = $this;
    $args[] = &$state;
    return $args;
  }

  protected function getSelectedVariable($selector, &$info, array $state) {
    if ($wrapper = rules_get_metadata_wrapper($selector, $state)) {
      $options = array('decode' => TRUE);
      if ($info['type'] == 'text' && !empty($info['sanitized'])) {
        $options = array('sanitize' => TRUE);
        unset($info['sanitized']);
      }
      return $wrapper->value($options);
    }
    throw new RulesException('Unable to apply data selector "' . check_plain($selector) . '".');
  }

  public function __sleep() {
    return parent::__sleep() + drupal_map_assoc(array('elementName', 'settings'));
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
        'files' => array(),
      );
      unset($info['arguments'], $info['new variables']);
      $info['callbacks'] += array('execute' => $info['base']);

      // Build up the per plugin implementation faces cache.
      foreach ($this->faces as $interface) {
        $methods = $file_names = array();

        foreach (get_class_methods($interface) as $method) {
          if (isset($info['callbacks'][$method]) && ($function = $info['callbacks'][$method])) {
            $methods[$method][0] = $function;
            $file_names[$method] = $this->getFileName($function, $info);
          }
          elseif (function_exists($function = $info['base'] . '_' . $method)) {
            $methods[$method][0] = $function;
            $file_names[$method] = $this->getFileName($function, $info);
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
   * Make sure supported destinations are included.
   */
  protected static function includeFiles() {
    static $included;

    if (!isset($included)) {
      foreach (module_implements('rules_file_info') as $module) {
        $files = (array)module_invoke($module, 'rules_file_info');
        // Automatically add "$module.rules_forms.inc" for each module.
        // rules.inc are already included thanks to the rules_hook_info() group.
        $files[] = $module . '.rules_forms';
        foreach ($files as $name) {
          module_load_include('inc', $module, $name);
        }
      }
      $included = TRUE;
    }
  }

  protected function getFileName($function, $info) {
    $reflector = new ReflectionFunction($function);
    $file = $reflector->getFileName();
    $path = drupal_get_path('module', $info['module']);
    if (strpos($file, '.inc') && strpos($file, $path) !== FALSE) {
      // Extract the filename without the trailing .inc.
      $start = strpos($file, $path) + strlen($path) + 1;
      return substr($file, $start, strlen($file) - $start - 4);
    }
  }
}

/**
 * Plugin UI Interface.
 */
interface RulesPluginUiInterface {

  public function form();
  public function form_validate();
  public function form_submit();
  public function render();
  public function help();

  /**
   * Checks whether the user has access to configure this plugin.
   */
  public function access();

  /**
   * Returns ui operations for this plugin.
   * //TODO: allow modules to specify them in the plugin_info hook.
   */
  public function operations();
}

/**
 * Provides the interface used for implementing an abstract plugin by using
 * the Faces extension mechanism.
 */
interface RulesPluginImplInterface extends RulesPluginUiInterface {

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
}

/**
 * Class providing default implementations for the concrete plugin if not
 * overriden by the implementation.
 */
class RulesAbstractPluginDefaults extends FacesExtender implements RulesPluginImplInterface {

  public function execute() {
    throw new RulesException($this->object->getPluginName() .": Execution implementation is missing.");
  }

  public function validate() {
    //TODO: Make sure used variables are available.
    //TODO: Make sure used values are allowed. (key/value pairs + allowed values)
  }

  public function process() {}
  public function form() {}
  public function form_validate() {}
  public function form_submit() {}
  public function access() {}
  public function operations() {}
  public function render() {}
  public function help() {}
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
}

interface TriggerableInterface {

  /**
   * Returns a reference on the array of event names associated with this
   * object.
   */
  public function &events();

  /**
   * Adds the specified event.
   *
   * @return TriggerableInterface
   */
  public function event($event);
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
  protected $info = array();

  public function __construct($variables = NULL) {
    if (isset($variables)) {
      $this->info['variables'] = $variables;
    }
  }

  /**
   * Allow access to the children through the iterator.
   */
  public function getIterator() {
    return new RulesRecursiveElementIterator($this->children);
  }

  public function integrityCheck() {
    if (isset($this->info['variables']) && isset($this->parent)) {
      throw new RulesException('%plugin: Specifying state variables is not possible for child elements.', array('%plugin' => $this->getPluginName()));
    }
    foreach ($this->children as $child) {
      $child->integrityCheck();
    }
  }

  public function dependencies() {
    $args = array();
    foreach ($this->children as $child) {
      $args[] = $child->dependencies();
    }
    return !empty($args) ? call_user_func_array('array_merge', $args) : array();
  }

  public function parameterInfo() {
    if (isset($this->info['variables'])) {
      return rules_filter_array($this->info['variables'], 'handler', FALSE);
    }
    throw new RulesException('%plugin: Unable to execute container without specified variables.', array('%plugin' => $this->getPluginName()));
  }

  public function providesVariables() {
    $provided = array();
    foreach ($this->children as $key => $child) {
      $provided += $child->providesVariables();
    }
    return $provided;
  }

  protected function stateVariables() {
    if (!isset($this->parent) || (!empty($this->state) && $this->state['executed'] === $this)) {
      return isset($this->info['variables']) ? $this->info['variables'] : array();
    }
    return $this->availableVariables();
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
   * Note: Action container can never return something, but condition
   * containers have to return a boolean.
   */
  public function execute() {
    $replacements = array('%label' => isset($this->info['label']) ? $this->info['label'] : $this->name, '%plugin' => $this->itemName);
    rules_log('Executing %plugin %label.', $replacements, RulesLog::INFO, TRUE);
    $state = $this->setUpState(func_get_args());
    $return = $this->evaluate($state);
    $this->cleanUp();
    rules_log('Finished executing of %plugin %label.', $replacements, RulesLog::INFO, FALSE);
    return $return;
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
        $this->parent[] = $child;
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
}

/**
 * Base class for all action containers.
 */
abstract class RulesActionContainer extends RulesContainerPlugin implements RulesActionInterface {

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
    $this->children[] = $action;
    return $this;
  }

  /**
   * As default we add provided variables to the parent's scope.
   */
  protected function evaluate(array &$state) {
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
    $this->children[] = $condition;
    return $this;
  }

  /**
   * Negate this condition.
   *
   * @return RulesConditionContainer
   */
  public function negate($negate = TRUE) {
    $this->negate = $negate;
    return $this;
  }

  public function __sleep() {
    return parent::__sleep() + array('negate' => 'negate');
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
  protected function renderHelper(&$line = 0, $startTime = NULL) {
    if (!isset($startTime)) {
      $startTime = isset($this->log[$line][3]) ? $this->log[$line][3] : 0;
    }
    $output = array();
    while ($line < count($this->log)) {
      if ($output && !empty($this->log[$line][4])) {
        // The next entry stems from another evaluated set, add in its log messages here
        $output[] = $this->renderHelper($line, $startTime);
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

  public $msg, $args;

  function __construct($msg, $args = array()) {
    $this->msg = $msg;
    $this->args = $args;
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

/**
 * A small class mapping entity metadata wrappers to variables. We need this
 * map for being able to apply intelligent saving to this data.
 */
class RulesWrapperMap {

  protected $map = array();

  public function add(EntityMetadataWrapper $wrapper, RulesDataWrapper $var) {
    if ($id = $wrapper->getIdentifier()) {
      // Usually we map by the data's id, as when the wrapper's data has been
      // changed (e.g. node:author) then we need a new variable.
      $this->map[$id] = $var;
    }
    elseif ($var->isSavable()) {
      // Data being savable but having no ID can only be in a wrapper, if
      // directly derived from a variable. In that case we create a new data
      // item, which will be always associatd with that variable.
      $this->map[spl_object_hash($wrapper)] = $var;
    }
  }

  /**
   * Gets the variable for a wrapper.
   */
  public function getVariable(EntityMetadataWrapper $wrapper) {
    if (isset($this->map[spl_object_hash($wrapper)])) {
      return $this->map[spl_object_hash($wrapper)];
    }
    elseif (($id = $wrapper->getIdentifier()) && isset($this->map[$id])) {
      return $this->map[$id];
    }
    return FALSE;
  }

  /**
   * Get all variables contained in the map.
   */
  public function variables() {
    return $this->map;
  }
}
