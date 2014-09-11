<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Action\SaveEntityTest.
 */

namespace Drupal\Tests\rules\Unit\Action;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Action\SaveEntity;
use Drupal\Tests\rules\Unit\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\SaveEntity
 * @group rules_action
 */
class SaveEntityTest extends RulesUnitTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->action = new SaveEntity([], '', ['context' => [
      'entity' => new ContextDefinition('entity'),
      'immediate' => new ContextDefinition('boolean', NULL, FALSE),
    ]]);

    $this->action->setStringTranslation($this->getMockStringTranslation());
    $this->action->setTypedDataManager($this->getMockTypedDataManager());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Save entity', $this->action->summary());
  }

  /**
   * Tests the action execution when saving immediately.
   *
   * @covers ::execute()
   */
  public function testActionExecutionImmediately() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('save');

    $this->action->setContextValue('entity', $this->getMockTypedData($entity))
      ->setContextValue('immediate', $this->getMockTypedData(TRUE));

    $this->action->execute();
    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the entity has been saved already.');
  }

  /**
   * Tests the action execution when saving is postponed.
   *
   * @covers ::execute()
   */
  public function testActionExecutionPostponed() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->never())
      ->method('save');

    $this->action->setContextValue('entity', $this->getMockTypedData($entity));
    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['entity'], 'Action returns the entity context name for auto saving.');
  }

}
