<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\DataCalculateValue
 * @group RulesAction
 */
class DataCalculateValueTest extends RulesIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->action = $this->actionManager->createInstance('rules_data_calculate_value');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Calculates a numeric value', $this->action->summary());
  }

  /**
   * Tests the addition of two numeric values.
   *
   * @covers ::execute
   */
  public function testAdditionAction() {
    $input_1 = mt_rand();
    $input_2 = mt_rand();
    $this->action->setContextValue('input_1', $this->getTypedData('float', $input_1))
      ->setContextValue('operator', $this->getTypedData('string', '+'))
      ->setContextValue('input_2', $this->getTypedData('float', $input_2));
    $this->action->execute();
    $result = $this->action->getProvidedContext('result')->getContextValue();
    $this->assertEquals($input_1 + $input_2, $result, "Addition calculation correct");
  }

  /**
   * Tests the subtraction of one numeric value from another.
   *
   * @covers ::execute
   */
  public function testSubtractionAction() {
    $input_1 = mt_rand();
    $input_2 = mt_rand();
    $this->action->setContextValue('input_1', $this->getTypedData('float', $input_1))
      ->setContextValue('operator', $this->getTypedData('string', '-'))
      ->setContextValue('input_2', $this->getTypedData('float', $input_2));
    $this->action->execute();
    $result = $this->action->getProvidedContext('result')->getContextValue();
    $this->assertEquals($input_1 - $input_2, $result, "Subtraction calculation correct");
  }

  /**
   * Tests the multiplication of one numeric by another.
   *
   * @covers ::execute
   */
  public function testMultiplicationAction() {
    $input_1 = mt_rand();
    $input_2 = mt_rand();
    $this->action->setContextValue('input_1', $this->getTypedData('float', $input_1))
      ->setContextValue('operator', $this->getTypedData('string', '*'))
      ->setContextValue('input_2', $this->getTypedData('float', $input_2));
    $this->action->execute();
    $result = $this->action->getProvidedContext('result')->getContextValue();
    $this->assertEquals($input_1 * $input_2, $result, "Subtraction calculation correct");
  }

  /**
   * Tests the division of one numeric by another.
   *
   * @covers ::execute
   */
  public function testDivisionAction() {
    $input_1 = mt_rand();
    $input_2 = mt_rand();
    $this->action->setContextValue('input_1', $this->getTypedData('float', $input_1))
      ->setContextValue('operator', $this->getTypedData('string', '/'))
      ->setContextValue('input_2', $this->getTypedData('float', $input_2));
    $this->action->execute();
    $result = $this->action->getProvidedContext('result')->getContextValue();
    $this->assertEquals($input_1 / $input_2, $result, "Subtraction calculation correct");
  }

  /**
   * Tests the use of min operator for 2 input values.
   *
   * @covers ::execute
   */
  public function testMinimumAction() {
    $input_1 = mt_rand();
    $input_2 = mt_rand();
    $this->action->setContextValue('input_1', $this->getTypedData('float', $input_1))
      ->setContextValue('operator', $this->getTypedData('string', 'min'))
      ->setContextValue('input_2', $this->getTypedData('float', $input_2));
    $this->action->execute();
    $result = $this->action->getProvidedContext('result')->getContextValue();
    $this->assertEquals(min($input_1, $input_2), $result, "Min calculation correct");
  }

  /**
   * Tests the use of max operator for 2 input values.
   *
   * @covers ::execute
   */
  public function testMaximumAction() {
    $input_1 = mt_rand();
    $input_2 = mt_rand();
    $this->action->setContextValue('input_1', $this->getTypedData('float', $input_1))
      ->setContextValue('operator', $this->getTypedData('string', 'max'))
      ->setContextValue('input_2', $this->getTypedData('float', $input_2));
    $this->action->execute();
    $result = $this->action->getProvidedContext('result')->getContextValue();
    $this->assertEquals(max($input_1, $input_2), $result, "Max calculation correct");
  }

}
