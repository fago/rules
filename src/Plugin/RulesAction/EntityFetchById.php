<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\EntityFetchById.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Fetch entity by id' action.
 *
 * @RulesAction(
 *   id = "rules_entity_fetch_by_id",
 *   label = @Translation("Fetch entity by id"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity_type_id" = @ContextDefinition("string",
 *       label = @Translation("Entity type"),
 *       description = @Translation("Specifies the type of entity that should be fetched."),
 *       assignment_restriction = "input"
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
 * @todo: port for rules_entity_action_type_options.
 */
class EntityFetchById extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityFetchById object.
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
   * Executes the action with the given context.
   *
   * @param int $entity_type_id
   *   The entity type id.
   * @param int $entity_id
   *   The entity id.
   */
  public function doExecute($entity_type_id, $entity_id) {
    $storage = $this->entityManager->getStorage($entity_type_id);
    $entity = $storage->load($entity_id);
    // @todo Refine the provided context definition for 'entity'. Example: if
    //   the loaded entity is a node then the provided context definition should
    //  use the type node. We don't have an API for that yet.
    $this->setProvidedValue('entity', $entity);
  }

}
