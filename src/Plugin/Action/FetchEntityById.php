<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\FetchEntityById.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Fetch entity by id' action.
 *
 * @Action(
 *   id = "rules_fetch_entity_by_id",
 *   label = @Translation("Fetch entity by id"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity_type" = @ContextDefinition("string",
 *       label = @Translation("Entity type"),
 *       description = @Translation("Specifies the type of entity that should be fetched.")
 *     ),
 *     "entity_id" = @ContextDefinition("integer",
 *       label = @Translation("Identifier"),
 *       description = @Translation("The id of the entity that should be fetched.")
 *     )
 *   },
 *   provides = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class FetchEntityById extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a FetchEntityById object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Fetch entity by id');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $entity_type = $this->getContextValue('entity_type');
    $entity_id = $this->getContextValue('entity_id');
    $storage = $this->entityManager->getStorage($entity_type);
    $entity = $storage->load($entity_id);
    $this->setProvidedValue('entity', $entity);
  }
}
