<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfType
 * @group RulesCondition
 */
class EntityIsOfTypeTest extends RulesEntityIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_entity_is_of_type');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $entity = $this->prophesizeEntity(EntityInterface::class);
    $entity->getEntityTypeId()->willReturn('node')->shouldBeCalledTimes(2);

    // Add the test node to our context as the evaluated entity, along with an
    // explicit entity type string.
    // First, test with a value that should evaluate TRUE.
    $this->condition->setContextValue('entity', $entity->reveal())
      ->setContextValue('type', 'node');
    $this->assertTrue($this->condition->evaluate());

    // Then test with values that should evaluate FALSE.
    $this->condition->setContextValue('type', 'taxonomy_term');
    $this->assertFalse($this->condition->evaluate());
  }

}
