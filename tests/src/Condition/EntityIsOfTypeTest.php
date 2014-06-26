<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityIsOfBundleTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\EntityIsOfType;

/**
 * Tests the 'Entity is of type' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityIsOfType
 *
 * @see \Drupal\rules\Plugin\Condition\EntityIsOfType
 */
class EntityIsOfTypeTest extends ConditionTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Entity is of type condition test',
      'description' => 'Tests that an entity is of a particular type.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = new EntityIsOfType([], '', ['context' => [
      'entity' => new ContextDefinition('entity'),
      'type' => new ContextDefinition('string'),
    ]]);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
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
