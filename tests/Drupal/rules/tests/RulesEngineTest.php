<?php

/**
 * @file
 * Contains Drupal\rules\tests\RulesEngineTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\Action\Rule;
use Drupal\rules\Plugin\RulesPluginManager;
use Drupal\rules_test\Plugin\Condition\TestConditionTrue;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the core rules engine functionality.
 */
class RulesEngineTest extends UnitTestCase {

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
    $plugin_manager = new RulesPluginManager();
    $rule = new Rule(array(), 'test', array());
    $rule->condition(new TestConditionTrue())
      ->condition(new TestConditionTrue());
  }
}
