<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Action\SaveEntityTest.
 */

namespace Drupal\rules\Tests\Action;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Action\SaveEntity;
use Drupal\rules\Tests\RulesTestBase;

/**
 * Tests the 'Show message on the site' action.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Action\SaveEntity
 *
 * @see \Drupal\rules\Plugin\Action\SaveEntity
 */
class SaveEntityTest extends RulesTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Save entity',
      'description' => 'Tests the save entity action.',
      'group' => 'Rules actions',
    ];
  }

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
  }

}
