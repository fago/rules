<?php

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Engine\ActionExpressionInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Plugin\RulesExpression\ActionSet;
use Drupal\rules\Plugin\RulesExpression\RulesAction;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\ActionSet
 * @group Rules
 */
class ActionSetTest extends RulesUnitTestBase {

  /**
   * The action set being tested.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\ActionSet
   */
  protected $actionSet;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->actionSet = new ActionSet([], '', [], $this->expressionManager->reveal());
  }

  /**
   * Tests that an action in the set fires.
   */
  public function testActionExecution() {
    // The method on the test action must be called once.
    $this->testActionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $this->actionSet->addExpressionObject($this->testActionExpression->reveal())->execute();
  }

  /**
   * Tests that two actions in the set fire both.
   */
  public function testTwoActionExecution() {
    // The method on the test action must be called twice.
    $this->testActionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(1);

    $second_action = $this->prophesize(ActionExpressionInterface::class);
    $second_action->executeWithState(Argument::type(ExecutionStateInterface::class))
      ->shouldBeCalledTimes(1);
    $second_action->getUuid()->willReturn('uuid2');

    $this->actionSet->addExpressionObject($this->testActionExpression->reveal())
      ->addExpressionObject($second_action->reveal())
      ->execute();
  }

  /**
   * Tests that nested action sets work.
   */
  public function testNestedActionExecution() {
    // The method on the test action must be called twice.
    $this->testActionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(2);

    $inner = new ActionSet([], '', [], $this->expressionManager->reveal());
    $inner->addExpressionObject($this->testActionExpression->reveal());

    $this->actionSet->addExpressionObject($this->testActionExpression->reveal())
      ->addExpressionObject($inner)
      ->execute();
  }

  /**
   * Tests that a nested action can be retrieved by UUID.
   */
  public function testLookupAction() {
    $this->actionSet->addExpressionObject($this->testActionExpression->reveal());
    $uuid = $this->testActionExpression->reveal()->getUuid();
    $lookup_action = $this->actionSet->getExpression($uuid);
    $this->assertSame($this->testActionExpression->reveal(), $lookup_action);
    $this->assertFalse($this->actionSet->getExpression('invalid UUID'));
  }

  /**
   * Tests deleting an action from the container.
   */
  public function testDeletingAction() {
    $this->actionSet->addExpressionObject($this->testActionExpression->reveal());
    $second_action = $this->prophesize(RulesAction::class);
    $this->actionSet->addExpressionObject($second_action->reveal());

    // Get the UUID of the first action added.
    $uuid = $this->testActionExpression->reveal()->getUuid();
    $this->assertTrue($this->actionSet->deleteExpression($uuid));

    // Now only the second action remains.
    foreach ($this->actionSet as $action) {
      $this->assertSame($second_action->reveal(), $action);
    }
  }

}
