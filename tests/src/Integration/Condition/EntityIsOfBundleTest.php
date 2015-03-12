<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Condition\EntityIsOfBundleTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfBundle
 * @group rules_conditions
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
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Entity is of bundle', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->exactly(3))
      ->method('getEntityTypeId')
      ->will($this->returnValue('node'));

    $entity->expects($this->exactly(3))
      ->method('bundle')
      ->will($this->returnValue('page'));

    // Add the test node to our context as the evaluated entity, along with
    // explicit entity type and bundle strings.
    // First, test with values that should evaluate TRUE.
    $this->condition->setContextValue('entity', $entity)
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
