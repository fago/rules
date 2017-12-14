<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Url;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\EntityPathAliasCreate
 * @group RulesAction
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
   * @var \Drupal\Core\Path\AliasStorageInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Prepare mocked AliasStorageInterface.
    $this->aliasStorage = $this->prophesize(AliasStorageInterface::class);

    $this->container->set('path.alias_storage', $this->aliasStorage->reveal());

    // Instantiate the action we are testing.
    $this->action = $this->actionManager->createInstance('rules_entity_path_alias_create:entity:test');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Create test path alias', $this->action->summary());
  }

  /**
   * Tests the action execution with an unsaved entity.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithUnsavedEntity() {
    // Test that the alias is only saved once.
    $this->aliasStorage->save('test/1', 'about', 'en')->shouldBeCalledTimes(1);

    $entity = $this->getMockEntity();
    $entity->isNew()->willReturn(TRUE)->shouldBeCalledTimes(1);

    // Test that new entities are saved first.
    $entity->save()->shouldBeCalledTimes(1);

    $this->action->setContextValue('entity', $entity->reveal())
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
    $this->aliasStorage->save('test/1', 'about', 'en')->shouldBeCalledTimes(1);

    $entity = $this->getMockEntity();
    $entity->isNew()->willReturn(FALSE)->shouldBeCalledTimes(1);

    // Test that existing entities are not saved again.
    $entity->save()->shouldNotBeCalled();

    $this->action->setContextValue('entity', $entity->reveal())
      ->setContextValue('alias', 'about');

    $this->action->execute();
  }

  /**
   * Creates a mock entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Prophecy\Prophecy\ProphecyInterface
   *   The mocked entity object.
   */
  protected function getMockEntity() {
    $language = $this->languageManager->reveal()->getCurrentLanguage();

    $entity = $this->prophesizeEntity(EntityInterface::class);
    $entity->language()->willReturn($language)->shouldBeCalledTimes(1);

    $url = $this->prophesize(Url::class);
    $url->getInternalPath()->willReturn('test/1')->shouldBeCalledTimes(1);

    $entity->toUrl(Argument::any())->willReturn($url->reveal())
      ->shouldBeCalledTimes(1);

    return $entity;
  }

}
