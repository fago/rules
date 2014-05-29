<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\Rule.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Engine\RulesConditionInterface;

/**
 * Defines a rule, executing actions when conditions are met.
 *
 * @RulesExpression(
 *   id = "rules_rule",
 *   label = @Translation("A rule, executing actions when conditions are met.")
 * )
 */
class Rule extends PluginBase implements RuleInterface {

  /**
   * List of conditions that must be met before actions are executed.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface[]
   */
  protected $conditions = array();

  /**
   * List of actions that get executed if the conditions are met.
   *
   * @var \Drupal\Core\Action\ActionInterface[]
   */
  protected $actions = [];

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
   * {@inheritdoc}
   */
  public function addCondition(RulesConditionInterface $condition) {
    $this->conditions[] = $condition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    return $this->conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function addAction(ActionInterface $action) {
    $this->actions[] = $action;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getActions() {
    return $this->actions;
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // @todo: Implement.
  }

}
