<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\RulesExpression\Rule.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Condition\ConditionInterface;

/**
 * Defines a rule, executing actions when conditions are met.
 *
 * @RulesExpression(
 *   id = "rules_rule",
 *   label = @Translation("A rule, executing actions when conditions are met.")
 * )
 */
class Rule extends PluginBase implements ActionInterface {

  /**
   * List of conditions that must be met before actions are executed.
   *
   * @var array
   */
  protected $conditions = array();

  /**
   * List of actions that get executed if the conditions are met.
   *
   * @var array
   */
  protected $actions = array();

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Evaluate conditions, if they pass execute actions.
    foreach ($this->conditions as $condition) {
      if (!$condition->execute()) {
        // If a condition returns FALSE stop here.
        return;
      }
    }
    foreach ($this->actions as $action) {
      $action->execute();
    }
  }

  /**
   * Add a condition.
   *
   * @param ConditionInterface2 $condition
   *   The condition object.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\Rule
   *   The current rule object for chaining.
   */
  public function condition(ConditionInterface $condition) {
    $this->conditions[] = $condition;
    return $this;
  }

  /**
   * Adds an action.
   *
   * @param \Drupal\Core\Action\ActionInterface $action
   *   The action object to add.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\Rule
   *   The current rule object for chaining.
   */
  public function action(ActionInterface $action) {
    $this->actions[] = $action;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo: Implement.
  }

}
