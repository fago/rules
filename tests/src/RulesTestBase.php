<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesTestBase.
 */

namespace Drupal\rules\Tests;

use Drupal\Tests\UnitTestCase;

/**
 * Helper class with mock objects.
 */
abstract class RulesTestBase extends UnitTestCase {

  /**
   * A mocked condition that always evaluates to TRUE.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $trueCondition;

  /**
   * A mocked condition that always evaluates to FALSE.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $falseCondition;

  /**
   * A mocked dummy action object.
   *
   * @var \Drupal\Core\Action\ActionInterface
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
