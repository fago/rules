<?php

/**
 * @file
 * Contains \Drupal\rules_test\Plugin\Condition\TestConditionFalse.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\rules\Engine\RulesConditionContainer;

/**
 * Provides an always FALSE test condition.
 *
 * @Condition(
 *   id = "rules_test_false",
 *   label = @Translation("Test condition returning false")
 * )
 */
class TestConditionFalse extends RulesConditionContainer {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    return FALSE;
  }

}
