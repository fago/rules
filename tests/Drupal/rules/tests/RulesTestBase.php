<?php

/**
 * @file
 * Contains Drupal\rules\tests\RulesTestBase.
 */

namespace Drupal\rules\tests;

use Drupal\rules\Plugin\rules\Rule;
use Drupal\Tests\UnitTestCase;

/**
 * Helper class with mock objects.
 */
abstract class RulesTestBase extends UnitTestCase {

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
   * A mocked negated condition that always evaluates to FALSE.
   *
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $negatedTrueCondition;

  /**
   * A mocked negated condition that always evaluates to TRUE.
   *
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $negatedFalseCondition;

  /**
   * A mocked dummy action object.
   *
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $testAction;

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

    $this->trueCondition->expects($this->any())
      ->method('isNegated')
      ->will($this->returnValue(FALSE));

    $this->falseCondition = $this->getMockBuilder('Drupal\Core\Condition\ConditionPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $this->falseCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->falseCondition->expects($this->any())
      ->method('isNegated')
      ->will($this->returnValue(FALSE));

    $this->negatedTrueCondition = $this->getMockBuilder('Drupal\Core\Condition\ConditionPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $this->negatedTrueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->negatedTrueCondition->expects($this->any())
      ->method('isNegated')
      ->will($this->returnValue(TRUE));

    $this->negatedFalseCondition = $this->getMockBuilder('Drupal\Core\Condition\ConditionPluginBase')
      ->disableOriginalConstructor()
      ->getMock();

    $this->negatedFalseCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->negatedFalseCondition->expects($this->any())
      ->method('isNegated')
      ->will($this->returnValue(TRUE));

    $this->testAction = $this->getMockBuilder('Drupal\Core\Action\ActionBase')
      ->disableOriginalConstructor()
      ->getMock();
  }
}
