<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityIsOfBundleTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\EntityIsOfBundle;
use Drupal\rules\Tests\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfBundle
 * @group rules_conditions
 */
class EntityIsOfBundleTest extends RulesUnitTestBase {

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

    $this->condition = new EntityIsOfBundle([], '', ['context' => [
      'entity' => new ContextDefinition('entity'),
      'type' => new ContextDefinition('string'),
      'bundle' => new ContextDefinition('string'),
    ]]);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Entity is of bundle', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
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
    $this->condition->setContextValue('entity', $this->getMockTypedData($entity))
      ->setContextValue('type', $this->getMockTypedData('node'))
      ->setContextValue('bundle', $this->getMockTypedData('page'));

    $this->assertTrue($this->condition->evaluate());

    // Then test with values that should evaluate FALSE.
    $this->condition->setContextValue('bundle', $this->getMockTypedData('article'));
    $this->assertFalse($this->condition->evaluate());

    $this->condition->setContextValue('type', $this->getMockTypedData('taxonomy_term'))
      ->setContextValue('bundle', $this->getMockTypedData('page'));
    $this->assertFalse($this->condition->evaluate());
  }
}
