<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Integration;

use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Base class for Rules integration tests with entities.
 *
 * This base class makes sure the base-entity system is available and its data
 * types are registered. It enables entity_test module, such that some test
 * entity types are available.
 */
abstract class RulesEntityIntegrationTestBase extends RulesIntegrationTestBase {

  /**
   * The language manager mock.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $languageManager;

  /**
   * The mocked entity access handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entityAccess;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();

    $this->enabledModules['entity_test'] = TRUE;
    require_once $this->root . '/core/includes/entity.inc';

    $this->namespaces['Drupal\\Core\\Entity'] = $this->root . '/core/lib/Drupal/Core/Entity';
    $this->namespaces['Drupal\\entity_test'] = $this->root . '/core/modules/system/tests/modules/entity_test/src';

    $language = $this->prophesize(LanguageInterface::class);
    $language->getId()->willReturn('en');

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($language->reveal());
    $this->languageManager->getLanguages()->willReturn([$language->reveal()]);

    $this->entityAccess = $this->prophesize(EntityAccessControlHandlerInterface::class);

    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManager')
      ->setMethods(['getAccessControlHandler', 'getBaseFieldDefinitions', 'getBundleInfo'])
      ->setConstructorArgs([
        $this->namespaces,
        $this->moduleHandler->reveal(),
        $this->cacheBackend,
        $this->languageManager->reveal(),
        $this->getStringTranslationStub(),
        $this->getClassResolverStub(),
        $this->typedDataManager,
        $this->getMock('Drupal\Core\KeyValueStore\KeyValueFactoryInterface'),
        $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
      ])
      ->getMock();

    $this->entityManager->expects($this->any())
      ->method('getAccessControlHandler')
      ->with($this->anything())
      ->will($this->returnValue($this->entityAccess->reveal()));

    // The base field definitions for entity_test aren't used, and would
    // require additional mocking.
    $this->entityManager->expects($this->any())
      ->method('getBaseFieldDefinitions')
      ->willReturn([]);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($this->anything())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->container->set('entity.manager', $this->entityManager);

    $this->moduleHandler->getImplementations('entity_type_build')
      ->willReturn([]);
  }

}
