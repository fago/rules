<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataComparison
 * @group RulesCondition
 */
class DataComparisonTest extends RulesIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_data_comparison');
  }

  /**
   * Tests evaluating the condition with the "equals" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorEquals() {
    // Test that when a boolean data does not equal a boolean value
    // and the operator is not set - should fallback to '=='.
    $this->condition
      ->setContextValue('data', TRUE)
      ->setContextValue('value', FALSE);
    $this->assertFalse($this->condition->evaluate());

    // Test that when both data and value are false booleans
    // and the operator is not set - should fallback to '=='.
    $this->condition
      ->setContextValue('data', FALSE)
      ->setContextValue('value', FALSE);
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string equals the value string and the operator
    // is '==', TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Llama')
      ->setContextValue('operation', '==')
      ->setContextValue('value', 'Llama');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string does not equal the value string and the
    // operation is '==', FALSE is returned.
    $this->condition
      ->setContextValue('data', 'Kitten')
      ->setContextValue('operation', '==')
      ->setContextValue('value', 'Llama');
    $this->assertFalse($this->condition->evaluate());

    // Test that when both data and value are false booleans and the operation
    // is '==', TRUE is returned.
    $this->condition
      ->setContextValue('data', FALSE)
      ->setContextValue('operation', '==')
      ->setContextValue('value', FALSE);
    $this->assertTrue($this->condition->evaluate());

    // Test that when a boolean data does not equal a boolean value
    // and the operation is '==', FALSE is returned.
    $this->condition
      ->setContextValue('data', TRUE)
      ->setContextValue('operation', '==')
      ->setContextValue('value', FALSE);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "contains" operation.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorContains() {
    // Test that when the data string contains the value string, and the
    // operation is 'CONTAINS', TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Big Llama')
      ->setContextValue('operation', 'contains')
      ->setContextValue('value', 'Llama');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string does not contain the value string, and
    // the operation is 'contains', TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Big Kitten')
      ->setContextValue('operation', 'contains')
      ->setContextValue('value', 'Big Kitten');
    $this->assertTrue($this->condition->evaluate());

    // Test that when a data array contains the value string, and the operation
    // is 'CONTAINS', TRUE is returned.
    $this->condition
      ->setContextValue('data', ['Llama', 'Kitten'])
      ->setContextValue('operation', 'contains')
      ->setContextValue('value', 'Llama');
    $this->assertTrue($this->condition->evaluate());

    // Test that when a data array does not contain the value array, and the
    // operation is 'CONTAINS', TRUE is returned.
    $this->condition
      ->setContextValue('data', ['Kitten'])
      ->setContextValue('operation', 'contains')
      ->setContextValue('value', ['Llama']);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "IN" operation.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorIn() {
    // Test that when the data string is 'IN' the value array, TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Llama')
      ->setContextValue('operation', 'IN')
      ->setContextValue('value', ['Llama', 'Kitten']);
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data array is not in the value array, and the
    // operation is 'IN', FALSE is returned.
    $this->condition
      ->setContextValue('data', ['Llama'])
      ->setContextValue('operation', 'IN')
      ->setContextValue('value', ['Kitten']);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "is less than" operation.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorLessThan() {
    // Test that when data is less than value and operation is '<',
    // TRUE is returned.
    $this->condition
      ->setContextValue('data', 1)
      ->setContextValue('operation', '<')
      ->setContextValue('value', 2);
    $this->assertTrue($this->condition->evaluate());

    // Test that when data is greater than value and operation is '<',
    // FALSE is returned.
    $this->condition
      ->setContextValue('data', 2)
      ->setContextValue('operation', '<')
      ->setContextValue('value', 1);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "is greater than" operation.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorGreaterThan() {
    // Test that when data is greater than value and operation is '>',
    // TRUE is returned.
    $this->condition
      ->setContextValue('data', 2)
      ->setContextValue('operation', '>')
      ->setContextValue('value', 1);
    $this->assertTrue($this->condition->evaluate());

    // Test that when data is less than value and operation is '>',
    // FALSE is returned.
    $this->condition
      ->setContextValue('data', 1)
      ->setContextValue('operation', '>')
      ->setContextValue('value', 2);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Data comparison', $this->condition->summary());
  }

  /**
   * @covers ::refineContextDefinitions
   */
  public function testRefineContextDefinitions() {
    // When a string is selected for comparison, the value must be string also.
    $this->condition->refineContextDefinitions([
      'data' => DataDefinition::create('string'),
    ]);
    $this->assertEquals('string', $this->condition->getContextDefinition('value')->getDataType());

    // IN operation requires a list of strings as value.
    $this->condition->setContextValue('operation', 'IN');
    $this->condition->refineContextDefinitions([
      'data' => DataDefinition::create('string'),
    ]);
    $this->assertEquals('string', $this->condition->getContextDefinition('value')->getDataType());
    $this->assertTrue($this->condition->getContextDefinition('value')->isMultiple());
  }

}
