<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\EntityHasField
 * @group RulesCondition
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
    $entity = $this->prophesizeEntity(ContentEntityInterface::class);
    $entity->hasField('existing-field')->willReturn(TRUE)
      ->shouldBeCalledTimes(1);
    $entity->hasField('non-existing-field')->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    $this->condition->setContextValue('entity', $entity->reveal());

    // Test with an existing field.
    $this->condition->setContextValue('field', 'existing-field');
    $this->assertTrue($this->condition->evaluate());

    // Test with a non-existing field.
    $this->condition->setContextValue('field', 'non-existing-field');
    $this->assertFalse($this->condition->evaluate());
  }

}
