<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesEntityIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Unit;
use Drupal\Core\Entity\EntityManager;

require_once DRUPAL_ROOT . '/core/includes/entity.inc';

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
    $this->extraNamespaces += array(
      // Register entity plugins.
      'Drupal\\Core\\Entity' => DRUPAL_ROOT . '/core/lib/Drupal/Core/Entity',
      // Add entity test entity types.
      'Drupal\\entity_test' => DRUPAL_ROOT . '/core/modules/system/tests/modules/entity_test/src',
    );
    $this->enabledModules['entity_test'] = TRUE;

    parent::setup();

    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');
    $language_manager->expects($this->any())
      ->method('getCurrentLanguage')
      ->will($this->returnValue((object) array('id' => 'en')));
    $language_manager->expects($this->any())
      ->method('getLanguages')
      ->will($this->returnValue(array('en' => (object) array('id' => 'en'))));

    $this->entityManager = new EntityManager(
      $this->namespaces,
      $this->moduleHandler,
      $this->cacheBackend,
      $language_manager,
      $this->getStringTranslationStub(),
      $this->getClassResolverStub(),
      $this->typedDataManager,
      $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface')
    );
    $this->container->set('entity.manager', $this->entityManager);

    $this->moduleHandler->expects($this->any())
      ->method('getImplementations')
      ->with('entity_type_build')
      ->willReturn([]);
  }

}
