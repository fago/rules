<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\EntityIsOfType.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfType
 * @group rules_conditions
 */
class EntityIsOfTypeTest extends RulesIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_entity_is_of_type');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Entity is of type', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->exactly(2))
      ->method('getEntityTypeId')
      ->will($this->returnValue('node'));

    // Add the test node to our context as the evaluated entity, along with an
    // explicit entity type string.
    // First, test with a value that should evaluate TRUE.
    $this->condition->setContextValue('entity', $this->getMockTypedData($entity))
      ->setContextValue('type', $this->getMockTypedData('node'));
    $this->assertTrue($this->condition->evaluate());

    // Then test with values that should evaluate FALSE.
    $this->condition->setContextValue('type', $this->getMockTypedData('taxonomy_term'));
    $this->assertFalse($this->condition->evaluate());
  }
}
