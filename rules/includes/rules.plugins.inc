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
  protected function executeCallback(array $args, array &$state = array()) {
    rules_log('Executing the action "%name".', array('%name' => $this->elementName));
    $return = $this->__call('execute', rules_unwrap_data($args, $state));
    $wrappers = array();

    if (is_array($return)) {
      foreach ($return as $name => $data) {
        // Update data variables.
        if (isset($args[$name]) && $args[$name] instanceof RulesDataWrapper) {
          $args[$name]->update($data);
        }
        elseif (isset($this->settings[$name . ':select']) && $wrapper = rules_get_metadata_wrapper($this->settings[$name . ':select'], $state)) {
          try {
            // Update the data value and keep the wrapper object for saving.
            $wrappers[$name] = $wrapper->set($data);
          }
          catch (EntityMetadataWrapperException $e) {
            throw new RulesException('Unable to modify data "@selector": ' . $e->getMessage(), array('@selector' => $this->settings[$name . ':select']));
          }
        }
        // Add provided variables.
        elseif (!isset($state['variables'][$name]) && isset($this->info['provides'][$name])) {
          $var_name = isset($this->settings[$name . ':var']) ? $this->settings[$name . ':var'] : $name;
          $this->addVariable($var_name, $data, $this->info['provides'][$name], $state);
        }
      }
    }
    // Save paramaters as defined in the parameter info.
    if ($return !== FALSE) {
      foreach ($this->info['parameter'] as $name => $info) {
        if (!empty($info['save'])) {
          if ($args[$name] instanceof RulesDataWrapper) {
            $args[$name]->saveChanges(FALSE, $state);
          }
          elseif (isset($wrappers[$name]) || ($wrappers[$name] = rules_get_metadata_wrapper($this->settings[$name . ':select'], $state))) {
            rules_metadata_wrapper_save_changes($wrappers[$name], $state);
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

  protected function executeCallback(array $args, array &$state = array()) {
    $return = (bool)$this->__call('execute', rules_unwrap_data($args, $state));
    rules_log('The condition "%name" evaluated to %bool', array('%name' => $this->elementName, '%bool' => $return ? 'TRUE' : 'FALSE'));
    return $this->negate ? !$return : $return;
  }

  public function __sleep() {
    return parent::__sleep() + array('negate' => 'negate');
  }
}

/**
 * An actual rule.
 * Note: A rule also implements the RulesActionInterface (inherited).
 */
class Rule extends RulesActionContainer {

  protected $conditions = NULL;
  protected $itemName = 'rule';
  protected $isEvaluated = 0;

  /**
   * Whether this rule may fire recursive. For safety, this defaults to FALSE.
   */
  public $recursion = FALSE;
  public $label = 'unlabelled';

  /**
   * Get an iterator over all contained conditions. Note that this iterator also
   * implements the ArrayAcces interface.
   *
   * @return RulesRecursiveElementIterator
   */
  public function conditions() {
    if (!isset($this->conditions)) {
      $this->conditions = rules_and();
      $this->conditions->setParent($this);
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
    return $this->getIterator();
  }

  /**
   * Add a condition. Pass either an instance of the RulesConditionInterface
   * or the arguments as needed by rules_condition().
   *
   * @return Rule
   *   Returns $this to support chained usage.
   */
  public function condition($name, $settings = array()) {
    if (!isset($this->conditions)) {
      $this->conditions = rules_and();
      $this->conditions->setParent($this);
    }
    $this->conditions->condition($name, $settings);
    return $this;
  }

  public function sortChildren($deep = FALSE) {
    if (isset($this->conditions)) {
      $this->conditions->sortChildren($deep);
    }
    parent::sortChildren($deep);
  }

  protected function evaluate(array &$state) {
    if (!empty($this->id) && isset($state['blocked'][$this->id])) {
      return rules_log('Not evaluating the rule %label to prevent recursion.', array('%label' => $this->label), RulesLog::INFO);
    }
    rules_log('Evaluating rule %label.', array('%label' => $this->label));
    if (empty($this->conditions) || $this->conditions->evaluate($state)) {
      // Block the rule to prevent recursion, if not allowed.
      if (!$this->recursion && $this->id) {
        $state['blocked'][$this->id] = TRUE;
      }
      // Evaluate the actions.
      parent::evaluate($state);
      if (!$this->recursion && $this->id) {
        unset($state['blocked'][$this->id]);
      }
    }
  }

  public function integrityCheck() {
    parent::integrityCheck();
    $this->conditions();
    $this->conditions->integrityCheck();
  }
}

/**
 * Represents rules getting triggered by events.
 */
class RulesReactionRule extends Rule implements TriggerableInterface {

  protected $itemName = 'reaction rule';
  protected $events = array();

  /**
   * Returns the array of events associated with that Rule.
   */
  public function &events() {
    return $this->events;
  }

  /**
   * @return RulesReactionRule
   */
  public function event($event) {
    $this->events[] = $event;
    return $this;
  }

  /**
   * Reaction rules can't add variables to the parent scope, so copy $state.
   */
  protected function evaluate(array &$state) {
    $copy = $state;
    parent::evaluate($copy);
  }

  public function providesVariables() {
    return array();
  }

  //TODO: Return as for the events suiting.
  /* TODO: There is no event info cache yet.
  public function stateVariables() {
    $cache = rules_get_cache();
    foreach ($this->events as $event) {
      if (!isset($vars)) {
        $vars = $cache['event_info'][$event]['variables'];
      }
      else {
        $vars = array_intersect_key($vars, $cache['event_info'][$event]['variables']);
      }
    }
    return isset($vars) ? $vars : array();
  }*/
}

/**
 * A logical AND.
 */
class RulesAnd extends RulesConditionContainer {

  protected $itemName = 'and';

  protected function evaluate(array &$state) {
    foreach ($this->children as $condition) {
      if (!$condition->evaluate($state)) {
        rules_log('AND evaluated to FALSE.');
        return $this->negate;
      }
    }
    rules_log('AND evaluated to TRUE.');
    return !$this->negate;
  }
}

/**
 * A logical OR.
 */
class RulesOr extends RulesConditionContainer {

  protected $itemName = 'or';

  protected function evaluate(array &$state) {
    foreach ($this->children as $condition) {
      if ($condition->evaluate($state)) {
        rules_log('OR evaluated to TRUE.');
        return !$this->negate;
      }
    }
    rules_log('OR evaluated to FALSE.');
    return $this->negate;
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
      $rule_set->destroy();
    }
  }
}

/**
 * This class is used for caching the rules to be evaluated per event.
 */
class RulesEventSet extends RulesRuleSet {

  protected $itemName = 'event set';

  public function __construct($info = array()) {
    $this->info = $info;
  }

  public function execute() {
    rules_log('Reacting on event %label.', array('%label' => $this->info['label']), RulesLog::INFO, TRUE);
    $state = $this->setUpState(func_get_args());
    $this->evaluate($state);
    $this->cleanUp();
    rules_log('Finished reacting on event %label.', array('%label' => $this->info['label']), RulesLog::INFO, FALSE);
    // TODO: improve that to show the log for admins or log to watchdog.
    RulesLog::logger()->checkLog();
    drupal_set_message(RulesLog::logger()->render());
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

      $event = new RulesEventSet($info);
      $event->name = $name;
      // Add all rules associated with this event
      $ids = db_query("SELECT c.id FROM {rules_config} c " .
        "INNER JOIN {rules_trigger} t ON c.id = t.id AND t.event = :event", array(':event' => $name))
        ->fetchCol();

      if ($ids) {
        foreach (rules_config_load_multiple($ids) as $rule) {
          $event->rule($rule);
        }
        $event->setSkipSetup();
        cache_set('event_' . $name, $event, 'cache_rules');
        $event->destroy();
        //TODO: make sure GC can destroy the object.
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
