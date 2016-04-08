<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Fetch entity by id' action.
 *
 * @RulesAction(
 *   id = "rules_entity_fetch_by_id",
 *   label = @Translation("Fetch entity by id"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type"),
 *       description = @Translation("Specifies the type of the entity that should be fetched."),
 *       assignment_restriction = "input"
 *     ),
 *     "entity_id" = @ContextDefinition("integer",
 *       label = @Translation("Identifier"),
 *       description = @Translation("The id of the entity that should be fetched.")
 *     )
 *   },
 *   provides = {
 *     "entity_fetched" = @ContextDefinition("entity",
 *       label = @Translation("Fetched entity")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: port for rules_entity_action_type_options.
 */
class EntityFetchById extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityFetchById object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions(array $selected_data) {
    if ($type = $this->getContextValue('type')) {
      $this->pluginDefinition['provides']['entity_fetched']->setDataType("entity:$type");
    }
  }

  /**
   * Executes the action with the given context.
   *
   * @param string $entity_type
   *   The entity type id.
   * @param int $entity_id
   *   The entity id.
   */
  protected function doExecute($entity_type, $entity_id) {
    $storage = $this->entityTypeManager->getStorage($entity_type);
    $entity = $storage->load($entity_id);

    $this->setProvidedValue('entity_fetched', $entity);
  }

}
