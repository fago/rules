<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\rules\RulesOr.
 */

namespace Drupal\rules\Plugin\rules;

use Drupal\Core\Condition\ConditionInterface;

/**
 * Evaluates a group of conditions with a logical OR.
 *
 * @Rules(
 *   id = "rules_or",
 *   label = @Translation("A logical Or condition")
 * )
 */
class RulesOr extends RulesConditionContainer {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    foreach ($this->conditions as $condition) {
      if ($condition->execute() && !$condition->isNegated()) {
        return TRUE;
      }
    }
    // An empty OR should return TRUE, otherwise all conditions evaluated to
    // FALSE and we return FALSE.
    return empty($this->conditions);
  }

}
