<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataListContains
 * @group RulesCondition
 */
class ListContainsTest extends RulesIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_list_contains');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {

    // Test array of string values.
    $list = ['One', 'Two', 'Three'];

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

    // Create array of mock entities.
    $entity_zero = $this->prophesizeEntity(EntityInterface::class);
    $entity_zero->id()->willReturn('entity_zero_id');

    $entity_one = $this->prophesizeEntity(EntityInterface::class);
    $entity_one->id()->willReturn('entity_one_id');

    $entity_two = $this->prophesizeEntity(EntityInterface::class);
    $entity_two->id()->willReturn('entity_two_id');

    $entity_three = $this->prophesizeEntity(EntityInterface::class);
    $entity_three->id()->willReturn('entity_three_id');

    $entity_four = $this->prophesizeEntity(EntityInterface::class);
    $entity_four->id()->willReturn('entity_four_id');

    // Test array of entities.
    $entity_list = [
      $entity_one->reveal(),
      $entity_two->reveal(),
      $entity_three->reveal(),
    ];

    // Test that the list of entities doesn't contain entity 'entity_zero'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_zero->reveal());
    $this->assertFalse($this->condition->evaluate());

    // Test that the list of entities contains entity 'entity_one'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_one->reveal());
    $this->assertTrue($this->condition->evaluate());

    // Test that the list of entities contains entity 'entity_two'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_two->reveal());
    $this->assertTrue($this->condition->evaluate());

    // Test that the list of entities contains entity 'entity_three'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_three->reveal());
    $this->assertTrue($this->condition->evaluate());

    // Test that the list of entities doesn't contain entity 'entity_four'.
    $this->condition
      ->setContextValue('list', $entity_list)
      ->setContextValue('item', $entity_four->reveal());
    $this->assertFalse($this->condition->evaluate());
  }

}
