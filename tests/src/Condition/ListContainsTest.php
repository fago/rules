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
use Drupal\rules\Tests\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\ListContains
 * @group rules_conditions
 */
class ListContainsTest extends RulesIntegrationTestBase {

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
    $this->condition = $this->conditionManager->createInstance('rules_list_contains');
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

    $list = array('One','Two','Three','Four');

    // Test that the list contains 'Two'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Two');
    $this->assertTrue($this->condition->evaluate());

    // Test that the list doesn't contain 'Five'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Five');
    $this->assertFalse($this->condition->evaluate());
  }
}
