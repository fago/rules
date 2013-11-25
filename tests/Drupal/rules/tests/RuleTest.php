<?php

/**
 * @file
 * Contains Drupal\rules\tests\RuleTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\rules\Rule;

/**
 * Tests the core rules engine functionality.
 */
class RuleTest extends RulesTestBase {

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
   * Tests that an action fires if a condition passes.
   */
  public function testActionExecution() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $rule = new Rule(array(), 'test', array());
    $rule->condition($this->trueCondition);
    $rule->action($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that an action does not fire if a condition fails.
   */
  public function testConditionFails() {
    // The execute method on the action must never be called.
    $this->testAction->expects($this->never())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $rule = new Rule(array(), 'test', array());
    $rule->condition($this->falseCondition);
    $rule->action($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that an action fires if a condition passes.
   */
  public function testTwoConditionsTrue() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $rule = new Rule(array(), 'test', array());
    $rule->condition($this->trueCondition);
    $rule->condition($this->trueCondition);
    $rule->action($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that an action does not fire if a condition fails.
   */
  public function testTwoConditionsFalse() {
    // The execute method on the action must never be called.
    $this->testAction->expects($this->never())
      ->method('execute');

    // Create a test rule, we don't care about plugin information in the
    // constructor.
    $rule = new Rule(array(), 'test', array());
    $rule->condition($this->trueCondition);
    $rule->condition($this->falseCondition);
    $rule->action($this->testAction);
    $rule->execute();
  }
}
