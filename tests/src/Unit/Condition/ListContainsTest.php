<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Condition\ListContainsTest.
 *
 */

namespace Drupal\Tests\rules\Unit\Condition;

use Drupal\Tests\rules\Unit\RulesIntegrationTestBase;

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

    // Test array of string values
    $list = array('One','Two','Three');

    // Test that the list doesn't contain 'Zero'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Zero');
    $this->assertFalse($this->condition->evaluate());

    // Test that the list contains 'One'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'One');
    $this->assertTrue($this->condition->evaluate());

    // Test that the list contains 'Two'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Two');
    $this->assertTrue($this->condition->evaluate());

    // Test that the list contains 'Three'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Three');
    $this->assertTrue($this->condition->evaluate());

    // Test that the list doesn't contain 'Four'.
    $this->condition
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Four');
    $this->assertFalse($this->condition->evaluate());

    // Create array of mock entities
    $entity_zero = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity_zero->expects($this->any())
      ->method('id')
      ->will($this->returnValue('entity_zero_id'));

    $entity_one = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity_one->expects($this->any())
      ->method('id')
      ->will($this->returnValue('entity_one_id'));

    $entity_two = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity_two->expects($this->any())
      ->method('id')
      ->will($this->returnValue('entity_two_id'));

    $entity_three = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity_three->expects($this->any())
      ->method('id')
      ->will($this->returnValue('entity_three_id'));

    $entity_four = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity_four->expects($this->any())
      ->method('id')
      ->will($this->returnValue('entity_four_id'));

    // Test array of entities
    $entity_list = array($entity_one,$entity_two,$entity_three);

    // Test that the list of entities doesn't contain entity 'entity_zero'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_zero);
    $this->assertFalse($this->condition->evaluate());

    // Test that the list of entities contains entity 'entity_one'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_one);
    $this->assertTrue($this->condition->evaluate());

    // Test that the list of entities contains entity 'entity_two'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_two);
    $this->assertTrue($this->condition->evaluate());

    // Test that the list of entities contains entity 'entity_three'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_three);
    $this->assertTrue($this->condition->evaluate());

    // Test that the list of entities doesn't contain entity 'entity_four'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_four);
    $this->assertFalse($this->condition->evaluate());
  }
}
