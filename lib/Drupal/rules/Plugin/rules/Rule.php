<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\rules\Rule.
 */

namespace Drupal\rules\Plugin\rules;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Condition\ConditionInterface;

/**
 * Container for consitions and actions.
 *
 * @Rules(
 *   id = "rules_rule",
 *   label = @Translation("Rule executing actions when conditions are met.")
 * )
 */
class Rule implements ActionInterface {

  /**
   * List of conditions that must be met before actions are executed.
   *
   * @var array
   */
  protected $conditions = array();

  public function execute() {
    // Evaluate conditions, if they pass execute actions.
  }

  /**
   * Add a condition.
   *
   * @param ConditionInterface2 $condition
   *   The condition object.
   *
   * @return \Drupal\rules\Plugin\Action\Rule
   *   The current rule object for chaining.
   */
  public function condition(ConditionInterface $condition) {
    $conditions[] = $condition;
    return $this;
  }

  public function executeMultiple(array $objects) {

  }

}
