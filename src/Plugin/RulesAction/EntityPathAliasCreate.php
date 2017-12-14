<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic 'Create entity path alias' action.
 *
 * @RulesAction(
 *   id = "rules_entity_path_alias_create",
 *   deriver = "Drupal\rules\Plugin\RulesAction\EntityPathAliasCreateDeriver",
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityPathAliasCreate extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The alias storage service.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * The entity type id.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Constructs an EntityPathAliasCreate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\AliasStorageInterface $alias_storage
   *   The alias storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AliasStorageInterface $alias_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->aliasStorage = $alias_storage;
    $this->entityTypeId = $plugin_definition['entity_type_id'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.alias_storage')
    );
  }

  /**
   * Creates entity path alias.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that should get an alias.
   * @param string $alias
   *   The alias to be created.
   */
  protected function doExecute(EntityInterface $entity, $alias) {
    // We need to save the entity before we can get its internal path.
    if ($entity->isNew()) {
      $entity->save();
    }

    $path = $entity->toUrl()->getInternalPath();
    $langcode = $entity->language()->getId();
    $this->aliasStorage->save($path, $alias, $langcode);
  }

}
