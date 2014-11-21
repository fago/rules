<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Integration;

/**
 * Base class for Rules integration tests with entities.
 *
 * This base class makes sure the base-entity system is available and its data
 * types are registered. It enables entity_test module, such that some test
 * entity types are available.
 */
abstract class RulesEntityIntegrationTestBase extends RulesIntegrationTestBase {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();

    $this->enabledModules['entity_test'] = TRUE;
    require_once $this->root . '/core/includes/entity.inc';

    $this->namespaces['Drupal\\Core\\Entity'] = $this->root . '/core/lib/Drupal/Core/Entity';
    $this->namespaces['Drupal\\entity_test'] = $this->root . '/core/modules/system/tests/modules/entity_test/src';

    $language = $this->getMock('Drupal\Core\Language\LanguageInterface');
    $language->expects($this->any())
      ->method('getId')
      ->willReturn('en');

    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');
    $language_manager->expects($this->any())
      ->method('getCurrentLanguage')
      ->willReturn($language);
    $language_manager->expects($this->any())
      ->method('getLanguages')
      ->willReturn([$language]);

    $this->entityAccess = $this->getMock('Drupal\Core\Entity\EntityAccessControlHandlerInterface');

    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManager')
      ->setMethods(['getAccessControlHandler', 'getBundleInfo'])
      ->setConstructorArgs([
        $this->namespaces,
        $this->moduleHandler,
        $this->cacheBackend,
        $language_manager,
        $this->getStringTranslationStub(),
        $this->getClassResolverStub(),
        $this->typedDataManager,
        $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface'),
        $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
      ])
      ->getMock();

    $this->entityManager->expects($this->any())
      ->method('getAccessControlHandler')
      ->with($this->anything())
      ->will($this->returnValue($this->entityAccess));

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($this->anything())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->container->set('entity.manager', $this->entityManager);

    $this->moduleHandler->expects($this->any())
      ->method('getImplementations')
      ->with('entity_type_build')
      ->willReturn([]);
  }

}
