<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\RulesAnd.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesConditionContainer;
use Drupal\rules\Engine\RulesExpressionInterface;

/**
 * Evaluates a group of conditions with a logical AND.
 *
 * @RulesExpression(
 *   id = "rules_and",
 *   label = @Translation("Condition set (AND)")
 * )
 */
class RulesAnd extends RulesConditionContainer implements RulesExpressionInterface {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    foreach ($this->conditions as $condition) {
      if (!$condition->execute()) {
        return FALSE;
      }
    }
    // An empty AND should return FALSE, otherwise all conditions evaluated to
    // TRUE and we return TRUE.
    return !empty($this->conditions);
  }

}
