<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Condition\EntityHasFieldTest.
 */

namespace Drupal\Tests\rules\Integration\Condition;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityHasField
 * @group rules_conditions
 */
class EntityHasFieldTest extends RulesEntityIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_entity_has_field');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
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
