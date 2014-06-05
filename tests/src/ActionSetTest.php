<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ActionSetTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests the action set functionality.
 */
class ActionSetTest extends RulesTestBase {

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

  /**
   * Tests that an action in the set fires.
   */
  public function testActionExecution() {
    // The method on the test action must be called once.
    $this->testAction->expects($this->once())
      ->method('execute');

    $this->getMockActionSet()
      ->addAction($this->testAction)
      ->execute();
  }

  /**
   * Tests that two actions in the set fire both.
   */
  public function testTwoActionExecution() {
    // The method on the test action must be called twice.
    $this->testAction->expects($this->exactly(2))
      ->method('execute');

    $this->getMockActionSet()
      ->addAction($this->testAction)
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

    $inner_set = $this->getMockActionSet()
      ->addAction($this->testAction);

    $this->getMockActionSet()
      ->addAction($this->testAction)
      ->addAction($inner_set)
      ->execute();
  }

}
