<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\RulesOr.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesConditionContainer;
use Drupal\rules\Engine\RulesState;

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
  public function executeWithState(RulesState $state) {
    foreach ($this->conditions as $condition) {
      if ($condition->executeWithState($state)) {
        return TRUE;
      }
    }
    // An empty OR should return TRUE, otherwise all conditions evaluated to
    // FALSE and we return FALSE.
    return empty($this->conditions);
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
