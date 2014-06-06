<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RuleTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Plugin\RulesExpression\Rule;

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
   * Tests the static create method.
   *
   * @covers ::create()
   */
  public function testStaticCreate() {
    $manager = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpressionPluginManager')
      ->disableOriginalConstructor()
      ->getMock();

    $manager->expects($this->at(0))
      ->method('createInstance')
      ->with('rules_and');

    $manager->expects($this->at(1))
      ->method('createInstance')
      ->with('rules_action_set');

    $typed_data = $this->getMockBuilder('Drupal\Core\TypedData\TypedDataManager')
      ->disableOriginalConstructor()
      ->getMock();

    $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
    $container->expects($this->at(0))
      ->method('get')
      ->with('typed_data_manager')
      ->will($this->returnValue($typed_data));
    $container->expects($this->at(1))
      ->method('get')
      ->with('plugin.manager.rules_expression')
      ->will($this->returnValue($manager));

    Rule::create($container, [], 'rules_rule', []);
  }

  /**
   * Tests that a rule is constructed with condition and action container.
   *
   * @covers ::__construct()
   */
  public function testContainersOnConstruct() {
    $manager = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpressionPluginManager')
      ->disableOriginalConstructor()
      ->getMock();

    $and = $this->getMockAnd();
    $manager->expects($this->at(0))
      ->method('createInstance')
      ->with('rules_and')
      ->will($this->returnValue($and));

    $action_set = $this->getMockActionSet();
    $manager->expects($this->at(1))
      ->method('createInstance')
      ->with('rules_action_set')
      ->will($this->returnValue($action_set));

    $typed_data = $this->getMockBuilder('Drupal\Core\TypedData\TypedDataManager')
      ->disableOriginalConstructor()
      ->getMock();

    $rule = new Rule([], 'rules_rule', [], $typed_data, $manager);
    $this->assertSame($and, $rule->getConditions());
    $this->assertSame($action_set, $rule->getActions());
  }

  /**
   * Tests the condition container setter and getter.
   *
   * @covers ::setConditions()
   * @covers ::getConditions()
   */
  public function testSetConditionsGetConditions() {
    $rule = $this->getMockRule();

    $or = $this->getMockOr();
    $rule->setConditions($or);
    $this->assertSame($or, $rule->getConditions());

    $and = $this->getMockAnd();
    $rule->setConditions($and);
    $this->assertSame($and, $rule->getConditions());
  }

  /**
   * Tests the condition container setter and getter.
   *
   * @covers ::setActions()
   * @covers ::getActions()
   */
  public function testSetActionsGetActions() {
    $rule = $this->getMockRule();

    $action_set = $this->getMockActionSet();
    $rule->setActions($action_set);
    $this->assertSame($action_set, $rule->getActions());
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
