<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\EntityHasFieldTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\EntityHasField;
use Drupal\rules\Tests\RulesTestBase;

/**
 * Tests the 'Entity has field' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityHasField
 *
 * @see \Drupal\rules\Plugin\Condition\ EntityHasField
 */
class EntityHasFieldTest extends RulesTestBase {

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
      'name' => 'Entity is of bundle condition test',
      'description' => 'Tests whether an entity is of a particular [type and] bundle.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = new EntityHasField([], '', ['context' => [
      'entity' => new ContextDefinition('entity'),
      'field' => new ContextDefinition('string'),
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
    $this->assertEquals('Entity has field', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $entity = $this->getMock('Drupal\Core\Entity\ContentEntityInterface');
    $entity->expects($this->exactly(2))
      ->method('hasField')
      ->will($this->returnValueMap([
        ['existing-field', TRUE],
        ['non-existing-field', FALSE],
      ]));

    $this->condition->setContextValue('entity', $entity);

    // Test with an existing field.
    $this->condition->setContextValue('field', $this->getMockTypedData('existing-field'));
    $this->assertTrue($this->condition->evaluate());

    // Test with a non-existing field.
    $this->condition->setContextValue('field', $this->getMockTypedData('non-existing-field'));
    $this->assertFalse($this->condition->evaluate());
  }
}
