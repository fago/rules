<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Integration;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Config\Entity\ConfigEntityType;
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

    // We need to support multiple entity types, including a test type:
    $type_info = [
      'test' => [
        'id' => 'test',
        'label' => 'Test',
        'entity_keys' => [
          'bundle' => 'bundle',
        ],
      ],
      'user' => [
        'id' => 'user',
        'label' => 'Test User',
        'entity_keys' => [
          'bundle' => 'user',
        ],
      ],
      'node' => [
        'id' => 'node',
        'label' => 'Test Node',
        'entity_keys' => [
          'bundle' => 'dummy',
        ],
      ],
    ];

    $type_array = [];

    foreach ($type_info as $type => $info) {
      $entity_type = new ContentEntityType($info);
      $type_array[$type] = $entity_type;
    }

    // We need a user_role mock as well.
    $role_entity_info = [
      'id' => 'user_role',
      'label' => 'Test Role',
    ];
    $role_type = new ConfigEntityType($role_entity_info);
    $type_array['user_role'] = $role_type;

    $this->entityManager->getDefinitions()
      ->willReturn($type_array);

    $this->entityAccess = $this->prophesize(EntityAccessControlHandlerInterface::class);

    $this->entityManager->getAccessControlHandler(Argument::any())
      ->willReturn($this->entityAccess->reveal());

    // The base field definitions for our test entity aren't used, and would
    // require additional mocking. It doesn't appear that any of our tests rely on this
    // for any other entity type that we are mocking.
    $this->entityManager->getBaseFieldDefinitions(Argument::any())->willReturn([]);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->getBundleInfo(Argument::any())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->moduleHandler->getImplementations('entity_type_build')
      ->willReturn([]);
  }

}
