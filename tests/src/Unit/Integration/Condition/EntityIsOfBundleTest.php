<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfBundle
 * @group RulesCondition
 */
class EntityIsOfBundleTest extends RulesEntityIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_entity_is_of_bundle');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $entity = $this->prophesizeEntity(EntityInterface::class);

    $entity->getEntityTypeId()->willReturn('node')->shouldBeCalledTimes(3);
    $entity->bundle()->willReturn('page')->shouldBeCalledTimes(3);

    // Add the test node to our context as the evaluated entity, along with
    // explicit entity type and bundle strings.
    // First, test with values that should evaluate TRUE.
    $this->condition->setContextValue('entity', $entity->reveal())
      ->setContextValue('type', 'node')
      ->setContextValue('bundle', 'page');

    $this->assertTrue($this->condition->evaluate());

    // Then test with values that should evaluate FALSE.
    $this->condition->setContextValue('bundle', 'article');
    $this->assertFalse($this->condition->evaluate());

    $this->condition->setContextValue('type', 'taxonomy_term')
      ->setContextValue('bundle', 'page');
    $this->assertFalse($this->condition->evaluate());
  }

}
