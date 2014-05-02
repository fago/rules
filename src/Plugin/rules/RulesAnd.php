<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\rules\RulesAnd.
 */

namespace Drupal\rules\Plugin\rules;

/**
 * Evaluates a group of conditions with a logical AND.
 *
 * @RulesElement(
 *   id = "rules_and",
 *   label = @Translation("A logical And condition")
 * )
 */
class RulesAnd extends RulesConditionContainer {

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
