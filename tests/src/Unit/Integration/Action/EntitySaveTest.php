<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntitySave
 * @group RulesAction
 */
class EntitySaveTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * The mocked entity used for testing.
   *
   * @var \Drupal\Core\Entity\EntityInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entity = $this->prophesizeEntity(EntityInterface::class);

    $this->action = $this->actionManager->createInstance('rules_entity_save');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Save entity', $this->action->summary());
  }

  /**
   * Tests the action execution when saving immediately.
   *
   * @covers ::execute
   */
  public function testActionExecutionImmediately() {
    $this->entity->save()->shouldBeCalledTimes(1);

    $this->action->setContextValue('entity', $this->entity->reveal())
      ->setContextValue('immediate', TRUE);

    $this->action->execute();
    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the entity has been saved already.');
  }

  /**
   * Tests the action execution when saving is postponed.
   *
   * @covers ::execute
   */
  public function testActionExecutionPostponed() {
    $this->entity->save()->shouldNotBeCalled();

    $this->action->setContextValue('entity', $this->entity->reveal());
    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['entity'], 'Action returns the entity context name for auto saving.');
  }

}
