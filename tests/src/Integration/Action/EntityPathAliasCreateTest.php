<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityPathAliasCreateTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\EntityPathAliasCreate
 * @group rules_actions
 */
class EntityPathAliasCreateTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * The mocked alias storage service.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Prepare mocked AliasStorageInterface.
    $this->aliasStorage = $this->getMockBuilder('Drupal\Core\Path\AliasStorageInterface')
      ->setMethods(['save'])
      ->getMockForAbstractClass();

    $this->container->set('path.alias_storage', $this->aliasStorage);

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_path_alias_create:entity:entity_test');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Create entity_test path alias', $this->action->summary());
  }

  /**
   * Tests the action execution with an unsaved entity.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithUnsavedEntity() {
    // Test that the alias is only saved once.
    $this->aliasStorage
      ->expects($this->once())
      ->method('save');

    $entity = $this->getMockEntity();
    $entity->expects($this->once())
      ->method('isNew')
      ->will($this->returnValue(TRUE));

    // Test that new entities are saved first.
    $entity->expects($this->once())
      ->method('save');

    $this->action->setContextValue('entity', $entity)
      ->setContextValue('alias', 'about');

    $this->action->execute();
  }

  /**
   * Tests the action execution with a saved entity.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithSavedEntity() {
    // Test that the alias is only saved once.
    $this->aliasStorage
      ->expects($this->once())
      ->method('save');

    $entity = $this->getMockEntity();
    $entity->expects($this->once())
      ->method('isNew')
      ->will($this->returnValue(FALSE));

    // Test that existing entities are not saved again.
    $entity->expects($this->never())
      ->method('save');

    $this->action->setContextValue('entity', $entity)
      ->setContextValue('alias', 'about');

    $this->action->execute();
  }

  /**
   * Creates a mock entity.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Entity\EntityInterface
   *   The mocked entity object.
   */
  protected function getMockEntity() {
    $language = $this->languageManager->getCurrentLanguage();

    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('language')
      ->will($this->returnValue($language));

    $url = $this->getMockBuilder('Drupal\Core\Url')
      ->disableOriginalConstructor()
      ->getMock();

    $url->expects($this->once())
      ->method('getInternalPath')
      ->will($this->returnValue('test/1'));

    $entity->expects($this->once())
      ->method('urlInfo')
      ->with('canonical')
      ->will($this->returnValue($url));

    $this->aliasStorage->expects($this->once())
      ->method('save')
      ->with('test/1', 'about', 'en');

    return $entity;
  }

}
