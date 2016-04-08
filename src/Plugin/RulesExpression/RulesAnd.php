<?php

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\ConditionExpressionContainer;
use Drupal\rules\Engine\ExecutionStateInterface;

/**
 * Evaluates a group of conditions with a logical AND.
 *
 * @RulesExpression(
 *   id = "rules_and",
 *   label = @Translation("Condition set (AND)"),
 *   form_class = "\Drupal\rules\Form\Expression\ConditionContainerForm"
 * )
 */
class RulesAnd extends ConditionExpressionContainer {

  /**
   * Returns whether there is a configured condition.
   *
   * @todo: Remove this once we added the API to access configured conditions.
   *
   * @return bool
   *   TRUE if there are no conditions, FALSE otherwise.
   */
  public function isEmpty() {
    return empty($this->conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(ExecutionStateInterface $state) {
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
  protected function allowsMetadataAssertions() {
    // If the AND is not negated, all child-expressions must be executed - thus
    // assertions can be added it.
    return !$this->isNegated();
  }

}
