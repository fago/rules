<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\RulesExpression\RulesOr.
 */

namespace Drupal\rules\Plugin\RulesExpression;
use Drupal\rules\Engine\RulesConditionContainer;

/**
 * Evaluates a group of conditions with a logical OR.
 *
 * @RulesExpression(
 *   id = "rules_or",
 *   label = @Translation("Condition set (OR)")
 * )
 */
class RulesOr extends RulesConditionContainer {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    foreach ($this->conditions as $condition) {
      if ($condition->execute()) {
        return TRUE;
      }
    }
    // An empty OR should return TRUE, otherwise all conditions evaluated to
    // FALSE and we return FALSE.
    return empty($this->conditions);
  }

}
