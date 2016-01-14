<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\ActionSetTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\Component\Uuid\Php;
use Drupal\rules\Plugin\RulesExpression\ActionSet;
use Drupal\rules\Plugin\RulesExpression\RulesAction;
use Drupal\rules\Engine\ExecutionStateInterface;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\ActionSet
 * @group rules
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

    $this->actionSet = new ActionSet([], '', [], $this->expressionManager->reveal(), new Php());
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
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(2);

    $this->actionSet->addExpressionObject($this->testActionExpression->reveal())
      ->addExpressionObject($this->testActionExpression->reveal())
      ->execute();
  }

  /**
   * Tests that nested action sets work.
   */
  public function testNestedActionExecution() {
    // The method on the test action must be called twice.
    $this->testActionExpression->executeWithState(
      Argument::type(ExecutionStateInterface::class))->shouldBeCalledTimes(2);

    $inner = new ActionSet([], '', [], $this->expressionManager->reveal(), new Php());
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
    $uuid = $this->actionSet->getIterator()->key();
    $this->assertSame($this->testActionExpression->reveal(), $this->actionSet->getExpression($uuid));
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
    $uuid = $this->actionSet->getIterator()->key();
    $this->actionSet->deleteExpression($uuid);
    // Now only the second action remains.
    foreach ($this->actionSet as $action) {
      $this->assertSame($second_action->reveal(), $action);
    }
  }

}
