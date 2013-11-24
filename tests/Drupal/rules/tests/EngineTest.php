<?php

/**
 * @file
 * Contains Drupal\rules\tests\EngineTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\Action\Rule;
use Drupal\rules_test\Plugin\Condition\TestConditionTrue;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the core rules engine functionality.
 */
class EngineTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Rules Engine tests',
      'description' => 'Test using the rules API to create and evaluate rules.',
      'group' => 'Rules',
    );
  }

  /**
   * Tests creating a rule and iterating over the rule elements.
   */
  public function testRuleCreation() {
    // Create a test rule, we don't care about plugin information.
    $rule = new Rule(array(), 'test', array());
    $condition = new TestConditionTrue();
  }
}
