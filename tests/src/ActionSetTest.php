<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ActionSetTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Plugin\RulesExpression\ActionSet;

/**
 * Tests the action set functionality.
 */
class ActionSetTest extends RulesTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The action set being tested.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\ActionSet
   */
  protected $actionSet;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Action set tests',
      'description' => 'Tests the ActionSet class',
      'group' => 'Rules',
    ];
  }

  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->actionSet = new ActionSet([], '', [], $this->typedDataManager);
  }

  /**
   * Tests that an action in the set fires.
   */
  public function testActionExecution() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    $this->actionSet->addAction($this->testAction)->execute();
  }

  /**
   * Tests that two actions in the set fire both.
   */
  public function testTwoActionExecution() {
    // The method on the test action must be called twice.
    $this->testAction->expects($this->exactly(2))
      ->method('execute');

    $this->actionSet->addAction($this->testAction)
      ->addAction($this->testAction)
      ->execute();
  }

  /**
   * Tests that nested action sets work.
   */
  public function testNestedActionExecution() {
    // The method on the test action must be called twice.
    $this->testAction->expects($this->exactly(2))
      ->method('execute');

    $inner = $this->getMockActionSet()
      ->addAction($this->testAction);

    $this->actionSet->addAction($this->testAction)
      ->addAction($inner)
      ->execute();
  }

}
