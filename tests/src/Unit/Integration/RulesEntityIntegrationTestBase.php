<?php

namespace Drupal\Tests\rules\Unit\Integration;

use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Field\FieldTypePluginManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\rules\Context\ContextDefinition;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

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
   * The field type manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManager
   */
  protected $fieldTypeManager;

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

      $this->entityTypeManager->getDefinition($type)
        ->willReturn($entity_type);
      $this->entityManager->getDefinition($type)
        ->willReturn($entity_type);
    }

    // We need a user_role mock as well.
    $role_entity_info = [
      'id' => 'user_role',
      'label' => 'Test Role',
    ];
    $role_type = new ConfigEntityType($role_entity_info);
    $type_array['user_role'] = $role_type;

    $this->entityTypeManager->getDefinitions()
      ->willReturn($type_array);
    $this->entityManager->getDefinitions()
      ->willReturn($type_array);

    $this->entityAccess = $this->prophesize(EntityAccessControlHandlerInterface::class);

    $this->entityTypeManager->getAccessControlHandler(Argument::any())
      ->willReturn($this->entityAccess->reveal());

    // The base field definitions for our test entity aren't used, and would
    // require additional mocking. It doesn't appear that any of our tests rely
    // on this for any other entity type that we are mocking.
    $this->entityFieldManager->getBaseFieldDefinitions(Argument::any())->willReturn([]);
    $this->entityManager->getBaseFieldDefinitions(Argument::any())->willReturn([]);

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityTypeBundleInfo->getBundleInfo(Argument::any())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->entityManager->getBundleInfo(Argument::any())
      ->willReturn(['test' => ['label' => 'Test']]);

    $this->moduleHandler->getImplementations('entity_type_build')
      ->willReturn([]);

    $this->fieldTypeManager = new FieldTypePluginManager(
      $this->namespaces, $this->cacheBackend, $this->moduleHandler->reveal(), $this->typedDataManager
    );
    $this->container->set('plugin.manager.field.field_type', $this->fieldTypeManager);
  }

  /**
   * Helper to mock a context definition with a mocked data definition.
   *
   * @param string $data_type
   *   The data type, example "entity:node".
   * @param \Prophecy\Prophecy\ProphecyInterface $data_definition
   *   A prophecy that represents a data definition object.
   *
   * @return \Drupal\rules\Context\ContextDefinition
   *   The context definition with the data definition prophecy in it.
   */
  protected function getContextDefinitionFor($data_type, ProphecyInterface $data_definition) {
    // Mock all the setter calls on the data definition that can be ignored.
    $data_definition->setLabel(Argument::any())->willReturn($data_definition->reveal());
    $data_definition->setDescription(Argument::any())->willReturn($data_definition->reveal());
    $data_definition->setRequired(Argument::any())->willReturn($data_definition->reveal());
    $data_definition->setLabel(Argument::any())->willReturn($data_definition->reveal());
    $data_definition->setConstraints(Argument::any())->willReturn($data_definition->reveal());

    $data_definition->getConstraints()->willReturn([]);
    $data_definition->getDataType()->willReturn($data_type);

    $original_definition = $this->typedDataManager->getDefinition($data_type);
    $data_definition->getClass()->willReturn($original_definition['class']);

    $context_definition = ContextDefinition::create($data_type);

    // Inject a fake typed data manger that will return our data definition
    // prophecy if asked for it in the ContextDefinition class.
    $typed_data_manager = $this->prophesize(TypedDataManagerInterface::class);
    $typed_data_manager->createDataDefinition($data_type)->willReturn($data_definition->reveal());
    $context_definition->setTypedDataManager($typed_data_manager->reveal());

    return $context_definition;
  }

}
