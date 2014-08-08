<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Action\DeleteEntityTest.
 */

namespace Drupal\rules\Tests\Action;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Action\DeleteEntity;
use Drupal\rules\Tests\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\DeleteEntity
 * @group rules_action
 */
class DeleteEntityTest extends RulesUnitTestBase {

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

    $this->action = new DeleteEntity([], '', ['context' => [
      'entity' => new ContextDefinition('entity'),
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
    $this->assertEquals('Delete entity', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute()
   */
  public function testActionExecution() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('delete');

    $this->action->setContextValue('entity', $this->getMockTypedData($entity));
    $this->action->execute();
  }

}
