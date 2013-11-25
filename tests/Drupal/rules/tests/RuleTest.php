<?php

/**
 * @file
 * Contains Drupal\rules\tests\RuleTest.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\rules\Rule;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the core rules engine functionality.
 */
class RuleTest extends UnitTestCase {

  /**
   * A mocked condition that always evaluates to TRUE.
   *
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $trueCondition;

  /**
   * A mocked condition that always evaluates to FALSE.
   *
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $falseCondition;

  /**
   * A mocked dummy action object.
   *
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $testAction;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->trueCondition = $this->getMockBuilder('Drupal\Core\Condition\ConditionPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $this->trueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->falseCondition = $this->getMockBuilder('Drupal\Core\Condition\ConditionPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $this->falseCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->testAction = $this->getMockBuilder('Drupal\Core\Action\ActionBase')
      ->disableOriginalConstructor()
      ->getMock();
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
