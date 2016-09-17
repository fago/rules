<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\rules\Context\ContextDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives entity create plugin definitions based on content entity types.
 *
 * @see \Drupal\rules\Plugin\RulesAction\EntityCreate
 */
class EntityCreateDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Creates a new EntityCreateDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('entity_type.manager'), $container->get('entity_field.manager'), $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Only allow content entities and ignore configuration entities.
      if (!$entity_type instanceof ContentEntityTypeInterface) {
        continue;
      }

      $this->derivatives[$entity_type_id] = [
        'label' => $this->t('Create a new @entity_type', ['@entity_type' => $entity_type->getLowercaseLabel()]),
        'category' => $entity_type->getLabel(),
        'entity_type_id' => $entity_type_id,
        'context' => [],
        'provides' => [
          'entity' => ContextDefinition::create("entity:$entity_type_id")
            ->setLabel($entity_type->getLabel())
            ->setRequired(TRUE),
        ],
      ] + $base_plugin_definition;
      // Add a required context for the bundle key, and optional contexts for
      // other required base fields. This matches the storage create() behavior,
      // where only the bundle requirement is enforced.
      $bundle_key = $entity_type->getKey('bundle');
      $this->derivatives[$entity_type_id]['bundle_key'] = $bundle_key;

      $base_field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($entity_type_id);
      foreach ($base_field_definitions as $field_name => $definition) {
        if ($field_name != $bundle_key && !$definition->isRequired()) {
          continue;
        }

        $item_definition = $definition->getItemDefinition();
        $type_definition = $item_definition->getPropertyDefinition($item_definition->getMainPropertyName());

        // If this is an entity reference then we expect the target type as
        // context.
        if ($type_definition instanceof DataReferenceDefinitionInterface) {
          $type_definition->getTargetDefinition();
        }
        $type = $type_definition->getDataType();

        $is_bundle = ($field_name == $bundle_key);
        $multiple = ($definition->getCardinality() === 1) ? FALSE : TRUE;

        $context_definition = ContextDefinition::create($type)
          ->setLabel($definition->getLabel())
          ->setRequired($is_bundle)
          ->setMultiple($multiple)
          ->setDescription($definition->getDescription());

        if ($is_bundle) {
          $context_definition->setAssignmentRestriction(ContextDefinition::ASSIGNMENT_RESTRICTION_INPUT);
        }

        $this->derivatives[$entity_type_id]['context'][$field_name] = $context_definition;
      }
    }

    return $this->derivatives;
  }

}
