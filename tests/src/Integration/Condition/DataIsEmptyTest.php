<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\DataIsEmptyTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataIsEmpty
 * @group rules_conditions
 */
class DataIsEmptyTest extends RulesIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
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
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    // Test a ComplexDataInterface object.
    $entity_adapter_empty = $this->prophesize(ComplexDataInterface::class);
    $entity_adapter_empty->isEmpty()->willReturn(TRUE)->shouldBeCalledTimes(1);

    $this->condition->getContext('data')->setContextData($entity_adapter_empty->reveal());
    $this->assertTrue($this->condition->evaluate());

    $entity_adapter_full = $this->prophesize(ComplexDataInterface::class);
    $entity_adapter_full->isEmpty()->willReturn(FALSE)->shouldBeCalledTimes(1);

    $this->condition->getContext('data')->setContextData($entity_adapter_full->reveal());
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
