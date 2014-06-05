<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RuleTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests the core rules engine functionality.
 */
class RuleTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Rule class tests',
      'description' => 'Test the Rule class',
      'group' => 'Rules',
    ];
  }

  /**
   * Tests that an action fires if a condition passes.
   */
  public function testActionExecution() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    $rule = $this->getMockRule();
    $rule->addCondition($this->trueCondition);
    $rule->addAction($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that an action does not fire if a condition fails.
   */
  public function testConditionFails() {
    // The execute method on the action must never be called.
    $this->testAction->expects($this->never())
      ->method('execute');

    $rule = $this->getMockRule();
    $rule->addCondition($this->falseCondition);
    $rule->addAction($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that an action fires if a condition passes.
   */
  public function testTwoConditionsTrue() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    $rule = $this->getMockRule();
    $rule->addCondition($this->trueCondition);
    $rule->addCondition($this->trueCondition);
    $rule->addAction($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that an action does not fire if a condition fails.
   */
  public function testTwoConditionsFalse() {
    // The execute method on the action must never be called.
    $this->testAction->expects($this->never())
      ->method('execute');

    $rule = $this->getMockRule();
    $rule->addCondition($this->trueCondition);
    $rule->addCondition($this->falseCondition);
    $rule->addAction($this->testAction);
    $rule->execute();
  }

  /**
   * Tests that nested rules are properly executed.
   */
  public function testNestedRules() {
    $this->testAction->expects($this->once())
      ->method('execute');

    $nested = $this->getMockRule()
      ->addCondition($this->trueCondition)
      ->addAction($this->testAction);

    $this->getMockRule()
      ->addCondition($this->trueCondition)
      ->addAction($nested)
      ->execute();
  }

}
