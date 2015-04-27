<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\EntityPathAliasCreate.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Path\AliasStorageInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic 'Create entity path alias' action.
 *
 * @Action(
 *   id = "rules_entity_path_alias_create",
 *   deriver = "Drupal\rules\Plugin\Action\EntityPathAliasCreateDeriver",
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
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t("Create @entity_type_id path alias", array('@entity_type_id' => $this->entityTypeId));
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $alias = $this->getContextValue('alias');
    $entity = $this->getContextValue('entity');

    // We need to save the entity before we can get its internal path.
    if ($entity->isNew()) {
      $entity->save();
    }

    $path = $entity->urlInfo()->getInternalPath();
    $langcode = $entity->language()->getId();
    $this->aliasStorage->save($path, $alias, $langcode);
  }
}
