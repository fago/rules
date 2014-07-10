<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\DataComparisonTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\DataComparison;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Tests\RulesTestBase;

/**
 * Tests the 'Data comparison' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataComparison
 *
 * @see \Drupal\rules\Plugin\Condition\DataComparison
 */
class DataComparisonTest extends RulesTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Data comparison condition tests',
      'description' => 'Tests that data comparisons are true.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = new DataComparison([], '', ['context' => [
      'data' => new ContextDefinition(),
      'op' => new ContextDefinition('string', NULL, FALSE),
      'value' => new ContextDefinition(),
    ]]);
    $this->condition->setStringTranslation($this->getMockStringTranslation());
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
   */
  public function testConditionEvaluationOperatorEquals() {
    // Test that when the data string equals the value string and the operator
    // is '==', TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData('Llama'))
      ->setContextValue('op', $this->getMockTypedData('=='))
      ->setContextValue('value', $this->getMockTypedData('Llama'));
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string does not equal the value string and the
    // operator is '==', FALSE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData('Kitten'))
      ->setContextValue('op', $this->getMockTypedData('=='))
      ->setContextValue('value', $this->getMockTypedData('Llama'));
    $this->assertFalse($this->condition->evaluate());

    // Test that when both data and value are false booleans and the operator
    // is '==', TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(FALSE))
      ->setContextValue('op', $this->getMockTypedData('=='))
      ->setContextValue('value', $this->getMockTypedData(FALSE));
    $this->assertTrue($this->condition->evaluate());

    // Test that when a boolean data does not equal a boolean value
    // and the operator is '==', FALSE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(TRUE))
      ->setContextValue('op', $this->getMockTypedData('=='))
      ->setContextValue('value', $this->getMockTypedData(FALSE));
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "contains" operator.
   */
  public function testConditionEvaluationOperatorContains() {
    // Test that when the data string contains the value string, and the operator
    // is 'CONTAINS', TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData('Big Llama'))
      ->setContextValue('op', $this->getMockTypedData('contains'))
      ->setContextValue('value', $this->getMockTypedData('Llama'));
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data string does not contain the value string, and
    // the operator is 'contains', TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData('Big Kitten'))
      ->setContextValue('op', $this->getMockTypedData('contains'))
      ->setContextValue('value', $this->getMockTypedData('Big Kitten'));
    $this->assertTrue($this->condition->evaluate());

    // Test that when a data array contains the value string, and the operator 
    // is 'CONTAINS', TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(['Llama', 'Kitten']))
      ->setContextValue('op', $this->getMockTypedData('contains'))
      ->setContextValue('value', $this->getMockTypedData('Llama'));
    $this->assertTrue($this->condition->evaluate());

    // Test that when a data array does not contain the value array, and the
    // operator is 'CONTAINS', TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(['Kitten']))
      ->setContextValue('op', $this->getMockTypedData('contains'))
      ->setContextValue('value', $this->getMockTypedData(['Llama']));
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "IN" operator.
   */
  public function testConditionEvaluationOperatorIN() {
    // Test that when the data string is 'IN' the value array, TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData('Llama'))
      ->setContextValue('op', $this->getMockTypedData('IN'))
      ->setContextValue('value', $this->getMockTypedData(['Llama', 'Kitten']));
    $this->assertTrue($this->condition->evaluate());

    // Test that when the data array is not in the value array, and the operator
    // is 'IN', FALSE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(['Llama']))
      ->setContextValue('op', $this->getMockTypedData('IN'))
      ->setContextValue('value', $this->getMockTypedData(['Kitten']));
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "is less than" operator.
   */
  public function testConditionEvaluationOperatorLessThan() {
    // Test that when data is less than value and operator is '<',
    // TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(1))
      ->setContextValue('op', $this->getMockTypedData('<'))
      ->setContextValue('value', $this->getMockTypedData(2));
    $this->assertTrue($this->condition->evaluate());

    // Test that when data is greater than value and operator is '<',
    // FALSE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(2))
      ->setContextValue('op', $this->getMockTypedData('<'))
      ->setContextValue('value', $this->getMockTypedData(1));
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "is greater than" operator.
   */
  public function testConditionEvaluationOperatorGreaterThan() {
    // Test that when data is greater than value and operator is '>',
    // TRUE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(2))
      ->setContextValue('op', $this->getMockTypedData('>'))
      ->setContextValue('value', $this->getMockTypedData(1));
    $this->assertTrue($this->condition->evaluate());

    // Test that when data is less than value and operator is '>',
    // FALSE is returned
    $this->condition
      ->setContextValue('data', $this->getMockTypedData(1))
      ->setContextValue('op', $this->getMockTypedData('>'))
      ->setContextValue('value', $this->getMockTypedData(2));
    $this->assertFalse($this->condition->evaluate());
  }

}
