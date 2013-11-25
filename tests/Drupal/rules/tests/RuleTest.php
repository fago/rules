<?php

/**
 * @file
 * Contains Drupal\rules\tests\RuleTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\rules\Rule;
use Drupal\rules\Plugin\rules\RulesOr;
use Drupal\rules_test\Plugin\Condition\TestConditionTrue;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the core rules engine functionality.
 */
class RuleTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Rule class tests',
      'description' => 'Test the Rule class',
      'group' => 'Rules',
    );
  }

  /**
   * Tests creating a rule and iterating over the rule elements.
   */
  public function testRuleCreation() {
    // Create a test rule, we don't care about plugin information.
    $rule = new Rule(array(), 'test', array());
    //$negated_condition = new TestConditionTrue();
    //$negated_condition->negate();
    $rule->condition(new TestConditionTrue())
      ->condition(new TestConditionTrue())
      ->condition((new RulesOr())
        ->condition(((new TestConditionTrue())->negate())));
  }
}
