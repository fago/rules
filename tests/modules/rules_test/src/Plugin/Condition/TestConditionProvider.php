<?php

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Test condition that adds a variable with the provided context.
 *
 * @Condition(
 *   id = "rules_test_provider",
 *   label = @Translation("Test condition provider"),
 *   provides = {
 *     "provided_text" = @ContextDefinition("string",
 *       label = @Translation("Provided text")
 *     )
 *   }
 * )
 */
class TestConditionProvider extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $this->setProvidedValue('provided_text', 'test value');
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // We don't care about summaries for test condition plugins.
    return '';
  }

}
