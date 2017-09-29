<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Drupal\user\UserInterface;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess
 * @group RulesCondition
 */
class UserHasEntityFieldAccessTest extends RulesEntityIntegrationTestBase {

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

    $this->enableModule('user');
    $this->condition = $this->conditionManager->createInstance('rules_entity_field_access');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    $account = $this->prophesizeEntity(UserInterface::class);
    $entity = $this->prophesizeEntity(ContentEntityInterface::class);
    $items = $this->prophesize(FieldItemListInterface::class);

    $entity->getEntityTypeId()->willReturn('user');
    $entity->hasField('potato-field')->willReturn(TRUE)
      ->shouldBeCalledTimes(3);

    $definition = $this->prophesize(FieldDefinitionInterface::class);
    $entity->getFieldDefinition('potato-field')
      ->willReturn($definition->reveal())
      ->shouldBeCalledTimes(2);

    $entity->get('potato-field')->willReturn($items->reveal())
      ->shouldBeCalledTimes(2);

    $this->condition->setContextValue('entity', $entity->reveal())
      ->setContextValue('field', 'potato-field')
      ->setContextValue('user', $account->reveal());

    $this->entityAccess->access($entity->reveal(), 'view', $account->reveal())
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);
    $this->entityAccess->access($entity->reveal(), 'edit', $account->reveal())
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);
    $this->entityAccess->access($entity->reveal(), 'delete', $account->reveal())
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    $this->entityAccess->fieldAccess('view', $definition->reveal(), $account->reveal(), $items->reveal())
      ->willReturn(TRUE)
      ->shouldBeCalledTimes(1);
    $this->entityAccess->fieldAccess('edit', $definition->reveal(), $account->reveal(), $items->reveal())
      ->willReturn(FALSE)
      ->shouldBeCalledTimes(1);

    // Test with 'view', 'edit' and 'delete'. Both 'view' and 'edit' will have
    // general entity access, but the 'potato-field' should deny access for the
    // 'edit' operation. Hence, 'edit' and 'delete' should return FALSE.
    $this->condition->setContextValue('operation', 'view');
    $this->assertTrue($this->condition->evaluate());
    $this->condition->setContextValue('operation', 'edit');
    $this->assertFalse($this->condition->evaluate());
    $this->condition->setContextValue('operation', 'delete');
    $this->assertFalse($this->condition->evaluate());
  }

}
