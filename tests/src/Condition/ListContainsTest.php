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

    $list = array('One','Two','Three','Four');

    // Test that the list contains 'Two'.
    $condition = $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Two');
    $this->assertTrue($condition->evaluate());

    // Test that the list doesn't contain 'Five'.
    $condition = $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Five');
    $this->assertFalse($condition->evaluate());
  }
}
