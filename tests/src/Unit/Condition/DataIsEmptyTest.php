<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Condition\DataIsEmptyTest.
 */

namespace Drupal\Tests\rules\Unit\Condition;

use Drupal\Tests\rules\Unit\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataIsEmpty
 * @group rules_conditions
 */
class DataIsEmptyTest extends RulesIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->condition = $this->conditionManager->createInstance('rules_data_is_empty');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Data value is empty', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $entity_adapter = $this->getMock('\Drupal\Core\TypedData\ComplexDataInterface');
    $entity_adapter->expects($this->at(0))
      ->method('isEmpty')
      ->will($this->returnValue(TRUE));
    $entity_adapter->expects($this->at(1))
      ->method('isEmpty')
      ->will($this->returnValue(FALSE));

    // Test a ComplexDataInterface object.
    $this->condition->getContext('data')->setContextData($entity_adapter);
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());

    // These should all return FALSE.
    // A non-empty array.
    $this->condition->getContext('data')->setContextData($this->getTypedData('list', [1,2,3]));
    $this->assertFalse($this->condition->evaluate());

    // An array containing an empty list.
    $this->condition->getContext('data')->setContextData($this->getTypedData('list', [[]]));
    $this->assertFalse($this->condition->evaluate());

    // An array with a zero-value element.
    $this->condition->getContext('data')->setContextData($this->getTypedData('list', [0]));
    $this->assertFalse($this->condition->evaluate());

    // A scalar value.
    $this->condition->getContext('data')->setContextData($this->getTypedData('integer', 1));
    $this->assertFalse($this->condition->evaluate());

    $this->condition->getContext('data')->setContextData($this->getTypedData('string', 'short string'));
    $this->assertFalse($this->condition->evaluate());

    // These should all return TRUE.
    // An empty array.
    $this->condition->getContext('data')->setContextData($this->getTypedData('list', []));
    $this->assertTrue($this->condition->evaluate());

    // The false/zero/NULL values.
    $this->condition->getContext('data')->setContextData($this->getTypedData('boolean', FALSE));
    $this->assertTrue($this->condition->evaluate());

    $this->condition->getContext('data')->setContextData($this->getTypedData('integer', 0));
    $this->assertTrue($this->condition->evaluate());

    $this->condition->getContext('data')->setContextData($this->getTypedData('string', NULL));
    $this->assertTrue($this->condition->evaluate());

    // An empty string.
    $this->condition->getContext('data')->setContextData($this->getTypedData('string', ''));
    $this->assertTrue($this->condition->evaluate());
  }

}
