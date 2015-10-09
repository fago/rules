<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\EntityCreate.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic 'Create a new entity' action.
 *
 * @RulesAction(
 *   id = "rules_entity_create",
 *   deriver = "Drupal\rules\Plugin\RulesAction\EntityCreateDeriver",
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityCreate extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The entity type id.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Constructs an EntityCreate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
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
      $container->get('entity.manager')->getStorage($plugin_definition['entity_type_id'])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $values = $this->getContextValues();
    $entity = $this->storage->create($values);
    $this->setProvidedValue('entity', $entity);
  }

}
