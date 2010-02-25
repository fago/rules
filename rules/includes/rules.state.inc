<?php
// $Id$

/**
 * @file Contains the state and data related stuff.
 */

/**
 * The rules evaluation state.
 */
class RulesState extends FacesRoot {

  /**
   * The known variables.
   */
  public $variables = array();

  /**
   * Holds info about the variables.
   */
  protected $info = array();

  /**
   * Keeps the ids of rules blocked due to recursion prevention.
   */
  protected $blocked = array();


  /**
   * The executed rules configuration.
   * @var RulesPlugin
   */
  protected $executedElement;

  /**
   * Keeps wrappers to be saved later on.
   */
  protected $save = array();

  /**
   * In case of recursive invocations this saves the previous state of the
   * executed configuration.
   */
  protected $oldState = NULL;

  /**
   * Holds the arguments while an element is executed. May be used by the
   * element to easily access the wrapped arguments.
   */
  public $currentArguments;


  public function __construct(RulesPlugin $element) {
    $this->executedElement = $element;
    $this->oldState = $element->property('state');
    $this->blocked = isset($this->oldState) ? $this->oldState->blocked : array();
  }

  /**
   * Adds the given variable to the given execution state.
   */
  public function addVariable($name, &$data, $info) {
    $this->info[$name] = $info + array(
      'skip save' => FALSE,
      'type' => 'unknown',
      'handler' => FALSE,
    );
    if (isset($data)) {
      // For unwrapped data be sure to the keep references!
      $this->variables[$name] =& rules_wrap_data($data, $this->info[$name]);
    }
  }

  /**
   * Runs post-evaluation tasks, such as saving variables.
   *
   * @return RulesState
   *   In case of recursive evaluations, the previous evaluation state.
   */
  public function cleanUp(RulesPlugin $element) {
    // Make changes permanent.
    foreach ($this->save as $selector => $wrapper) {
      $this->saveNow($selector);
    }
    unset($this->currentArguments);
    $state =& $element->property('state');
    $state = $this->oldState;
  }

  /**
   * Block a rules configuration from execution.
   */
  public function block($rules_config) {
    if (empty($rules_config->recursion) && $rules_config->id) {
      $this->blocked[$rules_config->id] = TRUE;
    }
  }

  /**
   * Unblock a rules configuration from execution.
   */
  public function unblock($rules_config) {
    if (empty($rules_config->recursion) && $rules_config->id) {
      unset($this->blocked[$rules_config->id]);
    }
  }

  /**
   * Returns whether a rules configuration should be blocked from execution.
   */
  public function isBlocked($rule_config) {
    return !empty($rule_config->id) && isset($this->blocked[$rule_config->id]);
  }

  /**
   * When cloning the evaluation state we want to have separate variables, so
   * that added variables won't appear in the original state. However we need
   * some arrays to be unique over all clones, so reference them properly.
   */
  public function __clone() {
    $state = $this->executedElement->property('state');
    $this->save =& $state->save;
    $this->blocked =& $state->blocked;
  }

  /**
   * Get the info about the state variables or a single variable.
   */
  public function varInfo($name = NULL) {
    if (isset($name)) {
      return isset($this->info[$name]) ? $this->info[$name] : FALSE;
    }
    return $this->info;
  }

  /**
   * Returns whether the given wrapper is savable.
   */
  public function isSavable($wrapper) {
    $entity_info = entity_get_info();
    return isset($entity_info[$wrapper->type()]['save callback']);
  }

  /**
   * Returns whether the variable with the given name is an entity.
   */
  public function isEntity($name) {
    $entity_info = entity_get_info();
    return isset($this->info[$name]['type']) && isset($entity_info[$this->info[$name]['type']]);
  }

  /**
   * Gets a variable.
   *
   * If necessary, the specified handler is invoked to fetch the variable.
   *
   * @param $name
   *   The name of the variable to return.
   * @return
   *   The variable or a EntityMetadataWrapper containing the variable.
   */
  public function &get($name) {
    if (!array_key_exists($name, $this->variables)) {
      if (!empty($this->info[$name]['handler'])) {
        $data = call_user_func_array($this->info[$name]['handler'], rules_unwrap_data($this->variables));
        $this->variables[$name] = rules_wrap_data($data, $this->info[$name]);
        $this->info[$name]['handler'] = FALSE;
        if (!isset($data)) {
          throw new RulesException('Unable to load variable %name, aborting.', array('%name' => $name), RulesLog::INFO);
        }
      }
      $this->variables += array($name => NULL);
    }
    return $this->variables[$name];
  }

  /**
   * Apply permanent changes provided the wrapper's data type is savable.
   *
   * @param $name
   *   The name of the variable to update.
   * @param $immediate
   *   Pass FALSE to postpone saving to later on. Else it's immediately saved.
   */
  public function saveChanges($selector, $wrapper, $immediate = FALSE) {
    $info = $wrapper->info();
    if (!$info['skip save'] && $this->isSavable($wrapper)) {
      $this->save($selector, $wrapper, $immediate);
    }
    // No entity, so try saving the parent.
    elseif (!$info['skip save'] && isset($info['parent']) && !($wrapper instanceof EntityDrupalWrapper)) {
      // Cut of the last part of the selector.
      $selector = implode(':', explode(':', $selector, -1));
      $this->saveChanges($selector, $info['parent'], $immediate);
    }
    return $this;
  }

  /**
   * Remembers to save the wrapper on cleanup or does it now.
   */
  protected function save($selector, EntityMetadataWrapper $wrapper, $immediate) {
    if (isset($this->save[$selector])) {
      if ($this->save[$selector][0]->getIdentifier() == $wrapper->getIdentifier()) {
        // The entity is already remembered. So do a combined save.
        $this->save[$selector][1] += $this->blocked;
      }
      else {
        // The wrapper is already in there, but wraps another entity. So first
        // save the old one, then care about the new one.
        $this->saveNow($selector);
      }
    }
    if (!isset($this->save[$selector])) {
      $this->save[$selector] = array(clone $wrapper, $this->blocked);
    }
    if ($immediate) {
      $this->saveNow($selector);
    }
  }

  /**
   * Saves the wrapper for the given selector.
   */
  protected function saveNow($selector) {
    // Add the set of blocked elements for the recursion prevention.
    $previously_blocked = $this->blocked;
    $this->blocked += $this->save[$selector][1];

    // Actually save!
    $wrapper = $this->save[$selector][0];
    rules_log('Saved %selector of type %type.', array('%selector' => $selector, '%type' => $wrapper->type()));
    $wrapper->save();

    // Restore the state's set of blocked elements.
    $this->blocked = $previously_blocked;
    unset($this->save[$selector]);
  }
}

/**
 * A class holding static methods related to data.
 */
class RulesData  {

  /**
   * Returns whether the type match. They match if type1 is compatible to type2.
   *
   * @param $var_info
   *   The name of the type to check for whether it is compatible to type2.
   * @param $param_info
   *   The type expression to check for.
   * @return
   *   Whether the types match.
   */
  public static function typesMatch($var_info, $param_info) {
    $var_type = $var_info['type'];
    $param_type = $param_info['type'];

    if ($param_type == '*' || $param_type == 'unknown') {
      return TRUE;
    }

    if ($var_type == $param_type) {
      // Make sure the bundle matches, if specified by the parameter.
      return !isset($param_info['bundles']) || isset($var_info['bundle']) && in_array($var_info['bundle'], $param_info['bundles']);
    }

    // Parameters may specify multiple types using an array.
    $valid_types = is_array($param_type) ? $param_type : array($param_type);
    if (in_array($var_type, $valid_types)) {
      return TRUE;
    }

    // Check for sub-type relationships.
    if (!isset($param_info['bundles'])) {
      $cache = &rules_get_cache();
      self::typeCalcAncestors($cache, $var_type);

      // If one of the types is an ancestor return TRUE.
      return (bool)array_intersect_key($cache['data_info'][$var_type]['ancestors'], array_flip($valid_types));
    }
    return FALSE;
  }

  protected static function typeCalcAncestors(&$cache, $type) {
    if (!isset($cache['data_info'][$type]['ancestors'])) {
      $cache['data_info'][$type]['ancestors'] = array();
      if (isset($cache['data_info'][$type]['parent']) && $parent = $cache['data_info'][$type]['parent']) {
        $cache['data_info'][$type]['ancestors'][$parent] = TRUE;
        self::typeCalcAncestors($cache, $parent);
        // Add all parent ancestors to our own ancestors.
        $cache['data_info'][$type]['ancestors'] += $cache['data_info'][$parent]['ancestors'];
      }
      // For special lists like list<node> add in "list" as valid parent.
      if (entity_metadata_list_extract_type($type)) {
        $cache['data_info'][$type]['ancestors']['list'] = TRUE;
      }
    }
  }
}

