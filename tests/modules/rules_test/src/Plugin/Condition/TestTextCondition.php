<?php

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Returns TRUE if the test parameter equals 'test value'.
 *
 * @Condition(
 *   id = "rules_test_string_condition",
 *   label = @Translation("Test condition using a string"),
 *   context = {
 *     "text" = @ContextDefinition("string",
 *       label = @Translation("Text to compare")
 *     )
 *   },
 *   configure_permissions = { "access test configuration" }
 * )
 */
class TestTextCondition extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $text = $this->getContextValue('text');
    return $text == 'test value';
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // We don't care about summaries for test condition plugins.
    return '';
  }

}
