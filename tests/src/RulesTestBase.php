<?php

/**
 * @file
 * Contains Drupal\rules\Tests\RulesTestBase.
 */

namespace Drupal\rules\Tests;

use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Engine\RulesConditionInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Helper class with mock objects.
 */
abstract class RulesTestBase extends UnitTestCase {

  /**
   * A mocked condition that always evaluates to TRUE.
   *
   * @var RulesConditionInterface
   */
  protected $trueCondition;

  /**
   * A mocked condition that always evaluates to FALSE.
   *
   * @var RulesConditionInterface
   */
  protected $falseCondition;

  /**
   * A mocked dummy action object.
   *
   * @var ActionInterface
   */
  protected $testAction;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->trueCondition = $this->getMockBuilder('Drupal\rules\Engine\RulesConditionContainer')
      ->disableOriginalConstructor()
      ->getMock();

    $this->trueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->falseCondition = $this->getMockBuilder('Drupal\rules\Engine\RulesConditionContainer')
      ->disableOriginalConstructor()
      ->getMock();

    $this->falseCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->testAction = $this->getMockBuilder('Drupal\Core\Action\ActionBase')
      ->disableOriginalConstructor()
      ->getMock();
  }
}
