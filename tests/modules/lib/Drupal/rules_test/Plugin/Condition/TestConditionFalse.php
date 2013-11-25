<?php

/**
 * @file
 * Contains Drupal\rules_test\Plugin\Condition\TestConditionFalse.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\rules\Plugin\rules\RulesConditionContainer;
use Drupal\rules\RulesConditionInterface;

/**
 * Provides an always FALSE test condition.
 *
 * @Condition(
 *   id = "rules_test_condition_false",
 *   label = @Translation("Test condition returning false")
 * )
 */
class TestConditionFalse extends RulesConditionContainer {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return FALSE;
  }

}
