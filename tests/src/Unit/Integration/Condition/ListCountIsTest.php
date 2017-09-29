<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataListCountIs
 * @group RulesCondition
 */
class ListCountIsTest extends RulesIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_list_count_is');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    // Test that the list count is greater than 2.
    $condition = $this->condition
      ->setContextValue('list', [1, 2, 3, 4])
      ->setContextValue('operator', '>')
      ->setContextValue('value', '2');
    $this->assertTrue($condition->evaluate());

    // Test that the list count is less than 4.
    $condition = $this->condition
      ->setContextValue('list', [1, 2, 3])
      ->setContextValue('operator', '<')
      ->setContextValue('value', '4');
    $this->assertTrue($condition->evaluate());

    // Test that the list count is equal to 3.
    $condition = $this->condition
      ->setContextValue('list', [1, 2, 3])
      ->setContextValue('operator', '==')
      ->setContextValue('value', '3');
    $this->assertTrue($condition->evaluate());

    // Test that the list count is equal to 0.
    $condition = $this->condition
      ->setContextValue('list', [])
      ->setContextValue('operator', '==')
      ->setContextValue('value', '0');
    $this->assertTrue($condition->evaluate());

    // Test that the list count is not less than 2.
    $condition = $this->condition
      ->setContextValue('list', [1, 2])
      ->setContextValue('operator', '<')
      ->setContextValue('value', '2');
    $this->assertFalse($condition->evaluate());

    // Test that list count is not greater than 5.
    $condition = $this->condition
      ->setContextValue('list', [1, 2, 3])
      ->setContextValue('operator', '>')
      ->setContextValue('value', '5');
    $this->assertFalse($condition->evaluate());

    // Test that the list count is not equal to 0.
    $condition = $this->condition
      ->setContextValue('list', [1, 2, 3])
      ->setContextValue('operator', '==')
      ->setContextValue('value', '0');
    $this->assertFalse($condition->evaluate());
  }

}
