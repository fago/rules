<?php
// $Id$

/**
 * @file Contains plugin info and implementations not needed for rule evaluation.
 */


/**
 * Implements a rules action.
 */
class RulesAction extends RulesAbstractPlugin implements RulesActionInterface {

  protected $itemName = 'action';

  /**
   * Execute the callback and update/save data as specified by the action.
   */
  protected function executeCallback(array $args, RulesState $state = NULL) {
    rules_log('Evaluating the action %name.', array('%name' => $this->elementName));
    $return = $this->__call('execute', rules_unwrap_data($args, $this->info['parameter']));

    if (is_array($return)) {
      foreach ($return as $name => $data) {
        // Update the data value using the wrapper.
        if (isset($args[$name]) && $args[$name] instanceof EntityMetadataWrapper) {
          try {
            $args[$name]->set($data);
          }
          catch (EntityMetadataWrapperException $e) {
            throw new RulesException('Unable to update the argument for parameter %name: ' . $e->getMessage(), array('@name' => $name), $this);
          }
        }
        elseif (isset($args[$name])) {
          $state->variables[$name] = $data;
        }
        // Add provided variables.
        elseif (!$state->varInfo($name) && isset($this->info['provides'][$name])) {
          $var_name = isset($this->settings[$name . ':var']) ? $this->settings[$name . ':var'] : $name;
          $state->addVariable($var_name, $data, $this->info['provides'][$name]);
          rules_log('Added the provided variable %name of type %type', array('%name' => $var_name, '%type' => $this->info['provides'][$name]['type']));
          if (!empty($this->info['provides'][$name]['save']) && $state->variables[$name] instanceof EntityDrupalWrapper) {
            $state->saveChanges($var_name, $state->variables[$name]);
          }
        }
      }
    }
    // Save paramaters as defined in the parameter info.
    if ($return !== FALSE) {
      foreach ($this->info['parameter'] as $name => $info) {
        if (!empty($info['save']) && $args[$name] instanceof EntityMetadataWrapper) {
          if (isset($this->settings[$name . ':select'])) {
            $state->saveChanges($this->settings[$name . ':select'], $args[$name]);
          }
          else {
            // Wrapper has been configured via direct input, so just save.
            rules_log('Saved argument of type %type for parameter %name.', array('%name' => $name, '%type' => $args[$name]->type()));
            $args[$name]->save();
          }
        }
      }
    }
  }
}

/**
 * Implements a rules condition.
 */
class RulesCondition extends RulesAbstractPlugin implements RulesConditionInterface {

  protected $itemName = 'condition';
  protected $negate = FALSE;

  public function providesVariables() {
    return array();
  }

  public function negate($negate = TRUE) {
    $this->negate = $negate;
    return $this;
  }

  protected function executeCallback(array $args, RulesState $state = NULL) {
    $return = (bool)$this->__call('execute', rules_unwrap_data($args));
    rules_log('The condition %name evaluated to %bool', array('%name' => $this->elementName, '%bool' => $return ? 'TRUE' : 'FALSE'));
    return $this->negate ? !$return : $return;
  }

  public function __sleep() {
    return parent::__sleep() + array('negate' => 'negate');
  }

  /**
   * Just return the boolean result.
   */
  protected function returnVariables(RulesState $state, $result = NULL) {
    return $result;
  }
}

/**
 * An actual rule.
 * Note: A rule also implements the RulesActionInterface (inherited).
 */
class Rule extends RulesActionContainer {

  protected $conditions = NULL;
  protected $itemName = 'rule';

  /**
   * Whether this rule may fire recursive. For safety, this defaults to FALSE.
   */
  public $recursion = FALSE;
  public $label = 'unlabeled';

  /**
   * Get an iterator over all contained conditions. Note that this iterator also
   * implements the ArrayAcces interface.
   *
   * @return RulesRecursiveElementIterator
   */
  public function conditions() {
    if (!isset($this->conditions)) {
      $this->conditions = rules_and();
      // Don't use setParent() to avoid having it added to the children.
      $this->conditions->parent = $this;
    }
    return $this->conditions->getIterator();
  }

  public function __sleep() {
    return parent::__sleep() + drupal_map_assoc(array('conditions', 'recursion', 'label'));
  }

  /**
   * Get an iterator over all contained actions. Note that this iterator also
   * implements the ArrayAcces interface.
   *
   * @return RulesRecursiveElementIterator
   */
  public function actions() {
    return parent::getIterator();
  }

  /**
   * Add a condition. Pass either an instance of the RulesConditionInterface
   * or the arguments as needed by rules_condition().
   *
   * @return Rule
   *   Returns $this to support chained usage.
   */
  public function condition($name, $settings = array()) {
    $this->conditions();
    $this->conditions->condition($name, $settings);
    return $this;
  }

  public function sortChildren($deep = FALSE) {
    if (isset($this->conditions)) {
      $this->conditions->sortChildren($deep);
    }
    parent::sortChildren($deep);
  }

  protected function evaluate(RulesState $state) {
    if ($state->isBlocked($this)) {
      return rules_log('Not evaluating the rule %label to prevent recursion.', array('%label' => $this->label), RulesLog::INFO);
    }
    rules_log('Evaluating rule %label.', array('%label' => $this->label));
    if (empty($this->conditions) || $this->conditions->evaluate($state)) {
      // Block the rule to prevent recursion, if not allowed.
      $state->block($this);
      parent::evaluate($state);
      $state->unblock($this);
    }
  }

  public function integrityCheck() {
    if (isset($this->conditions)) {
      $this->conditions->integrityCheck();
    }
    return parent::integrityCheck();
  }

  public function access() {
    return (!isset($this->conditions) || $this->conditions->access()) && parent::access();
  }

  public function dependencies() {
    $modules = !isset($this->conditions) ? array() : $this->conditions->dependencies();
    return array_keys(array_flip($modules) + array_flip(parent::dependencies()));
  }

  public function destroy() {
    if (isset($this->conditions)) {
      $this->conditions->destroy();
    }
    parent::destroy();
  }

  /**
   * @return RulesRecursiveElementIterator
   */
  public function getIterator() {
    $this->conditions();
    $array = array_merge(array($this->conditions), $this->children);
    return new RulesRecursiveElementIterator($array);
  }

  protected function stateVariables($element = NULL) {
    // Don't add in provided action variables for the conditions.
    if (isset($element) && $element === $this->conditions) {
      return $this->availableVariables();
    }
    return parent::stateVariables($element);
  }

  protected function exportChildren($key = NULL) {
    $export = array();
    if (isset($this->conditions) && $this->conditions->children) {
      $export = $this->conditions->exportChildren('IF');
    }
    return $export + parent::exportChildren('DO');
  }
}

/**
 * Represents rules getting triggered by events.
 */
class RulesReactionRule extends Rule implements RulesTriggerableInterface {

  protected $itemName = 'reaction rule';
  protected $events = array();

  /**
   * Returns the array of events associated with that Rule.
   */
  public function &events() {
    return $this->events;
  }

  /**
   * Removes an event from the rule configuration.
   *
   * @param $event
   *   The name of the event to remove.
   * @return RulesReactionRule
   */
  public function removeEvent($event) {
    if (($id = array_search($event, $this->events)) !== FALSE) {
      unset($this->events[$id]);
    }
    return $this;
  }

  /**
   * @return RulesReactionRule
   */
  public function event($event) {
    $this->events[] = $event;
    return $this;
  }

  /**
   * Reaction rules can't add variables to the parent scope, so clone $state.
   */
  protected function evaluate(RulesState $state) {
    $copy = clone $state;
    parent::evaluate($copy);
  }

  public function access() {
    $event_info = rules_fetch_data('event_info');
    foreach ($this->events as $event) {
      if (!empty($event_info[$event]['access callback']) && !call_user_func($event_info[$event]['access callback'], 'event', $event)) {
        return FALSE;
      }
    }
    return parent::access();
  }

  public function dependencies() {
    $modules = array_flip(parent::dependencies());
    $event_info = rules_fetch_data('event_info');
    foreach ($this->events as $event) {
      if (isset($event_info[$event]['module'])) {
        $modules[$event_info[$event]['module']] = TRUE;
      }
    }
    return array_keys($modules);
  }

  public function providesVariables() {
    return array();
  }

  public function argumentInfo() {
    // If executed directly, all variables as defined by the event need to
    // be passed.
    return $this->availableVariables();
  }

  public function availableVariables() {
    if (isset($this->parent)) {
      // Return the event variables provided by the event set, once cached.
      return $this->parent->stateVariables();
    }
    //TODO: Once we have event_info caching make use of it.
    $event_info = rules_fetch_data('event_info');
    $events = array_intersect($this->events, array_keys($event_info));
    foreach ($events as $event) {
      $event_info[$event] += array('variables' => array());
      if (!isset($vars)) {
        $vars = $event_info[$event]['variables'];
      }
      else {
        $vars = array_intersect_key($vars, $event_info[$event]['variables']);
      }
    }
    return isset($vars) ? $vars : array();
  }

  public function __sleep() {
    return parent::__sleep() + drupal_map_assoc(array('events'));
  }

  protected function exportChildren($key = 'ON') {
    $export[$key] = $this->events;
    return $export + parent::exportChildren();
  }
}

/**
 * A logical AND.
 */
class RulesAnd extends RulesConditionContainer {

  protected $itemName = 'and';

  protected function evaluate(RulesState $state) {
    foreach ($this->children as $condition) {
      if (!$condition->evaluate($state)) {
        rules_log('AND evaluated to FALSE.');
        return $this->negate;
      }
    }
    rules_log('AND evaluated to TRUE.');
    return !$this->negate;
  }

  public function label() {
    return !empty($this->label) ? $this->label : ($this->negate ? t('NOT AND') : t('AND'));
  }
}

/**
 * A logical OR.
 */
class RulesOr extends RulesConditionContainer {

  protected $itemName = 'or';

  protected function evaluate(RulesState $state) {
    foreach ($this->children as $condition) {
      if ($condition->evaluate($state)) {
        rules_log('OR evaluated to TRUE.');
        return !$this->negate;
      }
    }
    rules_log('OR evaluated to FALSE.');
    return $this->negate;
  }

  public function label() {
    return !empty($this->label) ? $this->label : ($this->negate ? t('NOT OR') : t('OR'));
  }
}

/**
 * A loop element.
 */
class RulesLoop extends RulesActionContainer {

  protected $itemName = 'loop';
  protected $parameter = array(
    'list' => array(
      'type' => 'list',
      'restriction' => 'selector',
    ),
  );

  public function __construct($settings = array(), $variables = NULL) {
    $this->setUp();
    $this->settings = (array)$settings + array(
      'item:var' => 'list-item',
      'item:label' => t('Current list item'),
    );
    if (!empty($variables)) {
      $this->info['variables'] = $variables;
    }
  }

  public function parameterInfo() {
    $info = $this->parameter;
    $info['list']['label'] = t('The list to loop over');
    return $info;
  }

  public function integrityCheck() {
    parent::integrityCheck();
    $this->checkVarName($this->info['item:var']);
  }

  protected function listItemType() {
    if (!isset($this->settings['item:type'])) {
      $info = $this->getArgumentInfo('list');
      $this->settings['item:type'] = isset($info['type']) ? entity_metadata_list_extract_type($info['type']) : 'unknown';
    }
    return $this->settings['item:type'];
  }

  protected function listItemInfo() {
    return array(
      'type' => $this->listItemType(),
      'label' => $this->settings['item:label'],
    );
  }

  protected function evaluate(RulesState $state) {
    $list_var = $this->getArgument('list', $this->parameter['list'], $state);
    $item_info = $this->listItemInfo();
    $name = $this->settings['item:var'];
    if (isset($this->settings['list:select'])) {
      rules_log('Looping over the list items of %selector', array('%selector' => $this->settings['list:select']));
    }

    // Loop over the list and evaluate the children for each list item.
    foreach ($list_var as $key => $item_value) {
      // Use a separate state so variables are available in the loop only.
      $state2 = clone $state;
      $state2->addVariable($name, $list_var[$key], $item_info);
      parent::evaluate($state2);
    }
  }

  protected function stateVariables($element = NULL) {
    return array($this->settings['item:var'] => $this->listItemInfo()) + parent::stateVariables($element);
  }

  public function label() {
    return !empty($this->label) ? $this->label : t('Loop');
  }

  protected function exportChildren($key = 'LOOP OVER') {
    return parent::exportChildren($key);
  }
}


/**
 * A set of rules to execute upon defined variables.
 */
class RulesRuleSet extends RulesActionContainer {

  protected $itemName = 'rule set';

  /**
   * @return RulesRuleSet
   */
  public function rule($rule) {
    return $this->action($rule);
  }

  public function rebuildCache(&$itemInfo, &$cache) {
    parent::rebuildCache($itemInfo, $cache);
    // Just move each rule-set into the cache.
    $sets = rules_config_load_multiple(FALSE, array('plugin' => 'rule set'));

    foreach ($sets as $id => $rule_set) {
      $rule_set->setSkipSetup();
      cache_set('set_' . $rule_set->name, $rule_set, 'cache_rules');
      // Set it back in case someone gets the object from cache.
      $rule_set->setSkipSetup(FALSE);
    }
  }

  protected function exportChildren($key = 'RULES') {
    return parent::exportChildren($key);
  }
}

/**
 * This class is used for caching the rules to be evaluated per event.
 */
class RulesEventSet extends RulesRuleSet {

  protected $itemName = 'event set';

  public function __construct($info = array()) {
    $this->setup();
    $this->info = $info;
  }

  public function execute() {
    rules_log('Reacting on event %label.', array('%label' => $this->info['label']), RulesLog::INFO, TRUE);
    $state = $this->setUpState(func_get_args());
    module_invoke_all('rules_config_execute', $this);
    $this->evaluate($state);
    $state->cleanUp($this);
    rules_log('Finished reacting on event %label.', array('%label' => $this->info['label']), RulesLog::INFO, FALSE);
    // TODO: improve that to show the log for admins or log to watchdog
    // in case of errors.
    RulesLog::logger()->checkLog();
  }

  public function rebuildCache(&$itemInfo, &$cache) {
    parent::rebuildCache($itemInfo, $cache);
    // Set up the per-event cache.
    $events = rules_fetch_data('event_info');
    $empty = array();

    foreach ($events as $name => $info) {
      $info += array(
        'variables' => isset($info['arguments']) ? $info['arguments'] : array(),
      );
      // Add all rules associated with this event to an EventSet for caching.
      if ($rules = rules_config_load_multiple(FALSE, array('event' => $name, 'active' => TRUE))) {
        $event = new RulesEventSet($info);
        $event->name = $name;
        foreach ($rules as $rule) {
          $event->rule($rule);
        }
        $event->setSkipSetup();
        cache_set('event_' . $name, $event, 'cache_rules');
        $event->destroy();
      }
      else {
        $empty[] = $name;
      }
    }
    // Cache a list of empty sets so we can use it to speed up later calls.
    // See rules_get_event_set().
    variable_set('rules_empty_sets', array_flip($empty));
  }
}
