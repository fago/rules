<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\RulesAnd.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\ConditionExpressionContainer;
use Drupal\rules\Engine\RulesState;

/**
 * Evaluates a group of conditions with a logical AND.
 *
 * @RulesExpression(
 *   id = "rules_and",
 *   label = @Translation("Condition set (AND)")
 * )
 */
class RulesAnd extends ConditionExpressionContainer {

  /**
   * Returns whether there is a configured condition.
   *
   * @todo: Remove this once we added the API to access configured conditions.
   *
   * @return bool
   */
  public function isEmpty() {
    return empty($this->conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(RulesState $state) {
    foreach ($this->conditions as $condition) {
      if (!$condition->executeWithState($state)) {
        return FALSE;
      }
    }
    // An empty AND should return FALSE, otherwise all conditions evaluated to
    // TRUE and we return TRUE.
    return !empty($this->conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $contexts = $this->getContexts();
    $state = new RulesState($contexts);
    return $this->executeWithState($state);
  }

}
