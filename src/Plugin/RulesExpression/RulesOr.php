<?php

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\ConditionExpressionContainer;
use Drupal\rules\Engine\ExecutionStateInterface;

/**
 * Evaluates a group of conditions with a logical OR.
 *
 * @RulesExpression(
 *   id = "rules_or",
 *   label = @Translation("Condition set (OR)")
 * )
 */
class RulesOr extends ConditionExpressionContainer {

  /**
   * {@inheritdoc}
   */
  public function evaluate(ExecutionStateInterface $state) {
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
  protected function allowsMetadataAssertions() {
    // We cannot garantuee child expressions are executed, thus we cannot allow
    // metadata assertions.
    return FALSE;
  }

}
