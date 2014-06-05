<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RuleTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests the core rules engine functionality.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\Rule
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
   * Tests that a rule is constructed with an 'and' condition container.
   *
   * @covers ::__construct()
   */
  public function testConditionContainerOnConstruct() {
    $manager = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpressionPluginManager')
      ->disableOriginalConstructor()
      ->getMock();

    $and = $this->getMockAnd();
    $manager->expects($this->once())
      ->method('createInstance')
      ->with('rules_and')
      ->will($this->returnValue($and));

    $rule = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\Rule')
      ->setMethods(NULL)
      ->setConstructorArgs([[], 'rules_rule', [], $manager])
      ->getMock();

    $this->assertSame($and, $rule->getConditions());
  }

  /**
   * Tests that an action fires if a condition passes.
   */
  public function testActionExecution() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    $this->getMockRule()
      ->addCondition($this->trueCondition)
      ->addAction($this->testAction)
      ->execute();
  }

  /**
   * Tests that an action does not fire if a condition fails.
   */
  public function testConditionFails() {
    // The execute method on the action must never be called.
    $this->testAction->expects($this->never())
      ->method('execute');

    $this->getMockRule()
      ->addCondition($this->falseCondition)
      ->addAction($this->testAction)
      ->execute();
  }

  /**
   * Tests that an action fires if a condition passes.
   */
  public function testTwoConditionsTrue() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    $this->getMockRule()
      ->addCondition($this->trueCondition)
      ->addCondition($this->trueCondition)
      ->addAction($this->testAction)
      ->execute();
  }

  /**
   * Tests that an action does not fire if a condition fails.
   */
  public function testTwoConditionsFalse() {
    // The execute method on the action must never be called.
    $this->testAction->expects($this->never())
      ->method('execute');

    $this->getMockRule()
      ->addCondition($this->trueCondition)
      ->addCondition($this->falseCondition)
      ->addAction($this->testAction)
      ->execute();
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
