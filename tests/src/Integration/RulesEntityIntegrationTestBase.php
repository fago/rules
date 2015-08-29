<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Integration;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Prophecy\Argument;

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

    require_once $this->root . '/core/includes/entity.inc';

    $this->namespaces['Drupal\\Core\\Entity'] = $this->root . '/core/lib/Drupal/Core/Entity';

    $language = $this->prophesize(LanguageInterface::class);
    $language->getId()->willReturn('en');

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($language->reveal());
    $this->languageManager->getLanguages()->willReturn([$language->reveal()]);

    $entityType = new ContentEntityType([
      'id' => 'test',
      'label' => 'Test',
      'entity_keys' => [
        'bundle' => 'bundle',
      ],
    ]);
    $this->entityManager->getDefinitions()
      ->willReturn(['test' => $entityType]);

    $this->entityAccess = $this->prophesize(EntityAccessControlHandlerInterface::class);

    $this->entityManager->getAccessControlHandler(Argument::any())
      ->willReturn($this->entityAccess->reveal());

    // The base field definitions for our test entity aren't used, and would
    // require additional mocking.
    $this->entityManager->getBaseFieldDefinitions('test')->willReturn([]);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->getBundleInfo(Argument::any())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->moduleHandler->getImplementations('entity_type_build')
      ->willReturn([]);
  }

}
