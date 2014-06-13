<?php

/**
 * @file
 * Contains \Drupal\rules_test\Plugin\Condition\TestConditionTrue.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\rules\Engine\RulesConditionBase;

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

}
