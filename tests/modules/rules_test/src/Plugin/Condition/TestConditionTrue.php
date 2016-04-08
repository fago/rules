<?php

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides an always TRUE test condition.
 *
 * @Condition(
 *   id = "rules_test_true",
 *   label = @Translation("Test condition returning true")
 * )
 */
class TestConditionTrue extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
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
