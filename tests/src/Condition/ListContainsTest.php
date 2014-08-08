<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\ListContainsTest.
 *
 * @todo: Add more testing, ensure test all types
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\ListContains;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Tests\RulesTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\ListContains
 * @group rules_conditions
 */
class ListContainsTest extends RulesTestBase {

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

    $this->condition = new ListContains([], '', ['context' => [
      'list' => new ContextDefinition('list'),
      'item' => new ContextDefinition(),
    ]]);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
    $this->condition->setTypedDataManager($this->getMockTypedDataManager());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('List contains', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    // Test that the list contains '2'.
    $condition = $this->condition
      ->setContextValue('list', $this->getMockTypedData([1,2,3,4]))
      ->setContextValue('item', $this->getMockTypedData('2'));
    $this->assertTrue($condition->evaluate());

    // Test that the list doesn't contain '5'.
    $condition = $this->condition
      ->setContextValue('list', $this->getMockTypedData([1,2,3,4]))
      ->setContextValue('item', $this->getMockTypedData('5'));
    $this->assertFalse($condition->evaluate());
  }
}
