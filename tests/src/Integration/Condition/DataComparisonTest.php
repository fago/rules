<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\DataComparisonTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataComparison
 * @group rules_conditions
 */
class DataComparisonTest extends RulesIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_data_comparison');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Data comparison', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition with the "equals" operator.
   *
   * @covers ::evaluate()
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
      ->setContextValue('operator', '==')
      ->setContextValue('value', 'Llama');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string does not equal the value string and the
    // operator is '==', FALSE is returned.
    $this->condition
      ->setContextValue('data', 'Kitten')
      ->setContextValue('operator', '==')
      ->setContextValue('value', 'Llama');
    $this->assertFalse($this->condition->evaluate());

    // Test that when both data and value are false booleans and the operator
    // is '==', TRUE is returned.
    $this->condition
      ->setContextValue('data', FALSE)
      ->setContextValue('operator', '==')
      ->setContextValue('value', FALSE);
    $this->assertTrue($this->condition->evaluate());

    // Test that when a boolean data does not equal a boolean value
    // and the operator is '==', FALSE is returned.
    $this->condition
      ->setContextValue('data', TRUE)
      ->setContextValue('operator', '==')
      ->setContextValue('value', FALSE);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "contains" operator.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationOperatorContains() {
    // Test that when the data string contains the value string, and the operator
    // is 'CONTAINS', TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Big Llama')
      ->setContextValue('operator', 'contains')
      ->setContextValue('value', 'Llama');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string does not contain the value string, and
    // the operator is 'contains', TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Big Kitten')
      ->setContextValue('operator', 'contains')
      ->setContextValue('value', 'Big Kitten');
    $this->assertTrue($this->condition->evaluate());

    // Test that when a data array contains the value string, and the operator
    // is 'CONTAINS', TRUE is returned.
    $this->condition
      ->setContextValue('data', ['Llama', 'Kitten'])
      ->setContextValue('operator', 'contains')
      ->setContextValue('value', 'Llama');
    $this->assertTrue($this->condition->evaluate());

    // Test that when a data array does not contain the value array, and the
    // operator is 'CONTAINS', TRUE is returned.
    $this->condition
      ->setContextValue('data', ['Kitten'])
      ->setContextValue('operator', 'contains')
      ->setContextValue('value', ['Llama']);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "IN" operator.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationOperatorIn() {
    // Test that when the data string is 'IN' the value array, TRUE is returned.
    $this->condition
      ->setContextValue('data', 'Llama')
      ->setContextValue('operator', 'IN')
      ->setContextValue('value', ['Llama', 'Kitten']);
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data array is not in the value array, and the operator
    // is 'IN', FALSE is returned.
    $this->condition
      ->setContextValue('data', ['Llama'])
      ->setContextValue('operator', 'IN')
      ->setContextValue('value', ['Kitten']);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "is less than" operator.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationOperatorLessThan() {
    // Test that when data is less than value and operator is '<',
    // TRUE is returned.
    $this->condition
      ->setContextValue('data', 1)
      ->setContextValue('operator', '<')
      ->setContextValue('value', 2);
    $this->assertTrue($this->condition->evaluate());

    // Test that when data is greater than value and operator is '<',
    // FALSE is returned.
    $this->condition
      ->setContextValue('data', 2)
      ->setContextValue('operator', '<')
      ->setContextValue('value', 1);
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "is greater than" operator.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationOperatorGreaterThan() {
    // Test that when data is greater than value and operator is '>',
    // TRUE is returned.
    $this->condition
      ->setContextValue('data', 2)
      ->setContextValue('operator', '>')
      ->setContextValue('value', 1);
    $this->assertTrue($this->condition->evaluate());

    // Test that when data is less than value and operator is '>',
    // FALSE is returned.
    $this->condition
      ->setContextValue('data', 1)
      ->setContextValue('operator', '>')
      ->setContextValue('value', 2);
    $this->assertFalse($this->condition->evaluate());
  }

}
