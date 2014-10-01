<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Condition\EntityHasFieldTest.
 */

namespace Drupal\Tests\rules\Unit\Condition;

use Drupal\Tests\rules\Unit\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityHasField
 * @group rules_conditions
 */
class EntityHasFieldTest extends RulesEntityIntegrationTestBase {

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
    $this->condition = $this->conditionManager->createInstance('rules_entity_has_field');
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
    $this->condition->setContextValue('field', 'existing-field');
    $this->assertTrue($this->condition->evaluate());

    // Test with a non-existing field.
    $this->condition->setContextValue('field', 'non-existing-field');
    $this->assertFalse($this->condition->evaluate());
  }
}
