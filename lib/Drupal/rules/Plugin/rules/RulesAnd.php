<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\rules\RulesAnd.
 */

namespace Drupal\rules\Plugin\rules;

/**
 * Evaluates a group of conditions with a logical AND.
 *
 * @Rules(
 *   id = "rules_and",
 *   label = @Translation("A logical And condition")
 * )
 */
class RulesAnd extends RulesConditionContainer {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    foreach ($this->conditions as $condition) {
      $result = $condition->execute();
      $negated = $condition->isNegated();
      if (($result && $negated) || (!$result && !$negated)) {
        return FALSE;
      }
    }
    // An empty AND should return FALSE, otherwise all conditions evaluated to
    // TRUE and we return TRUE.
    return !empty($this->conditions);
  }

}
