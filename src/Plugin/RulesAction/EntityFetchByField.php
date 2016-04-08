<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Fetch entities by field' action.
 *
 * @RulesAction(
 *   id = "rules_entity_fetch_by_field",
 *   label = @Translation("Fetch entities by field"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type"),
 *       description = @Translation("Specifies the type of the entity that should be fetched."),
 *     ),
 *     "field_name" = @ContextDefinition("string",
 *       label = @Translation("Field name"),
 *       description = @Translation("Name of the field by which the entity is to be selected.."),
 *     ),
 *     "field_value" = @ContextDefinition("any",
 *       label = @Translation("Field value"),
 *       description = @Translation("The field value of the entity to be fetched."),
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       description = @Translation("Limit the maximum number of fetched entities."),
 *       default_value = NULL,
 *       required = FALSE,
 *     ),
 *   },
 *   provides = {
 *      "entity_fetched" = @ContextDefinition("entity",
 *        label = @Translation("Fetched entity"),
 *        multiple = TRUE,
 *      )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityFetchByField extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityFetchByField object.
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
   * Execute the action within the given context.
   */
  protected function doExecute($entity_type, $field_name, $field_value, $limit = NULL) {
    $storage = $this->entityTypeManager->getStorage($entity_type);

    // When retrieving entities, if $limit is not set there is no need to use
    // the query object directly.
    if (is_null($limit)) {
      $entities = $storage->loadByProperties([$field_name => $field_value]);
    }
    else {
      $query = $storage->getQuery();
      $entity_ids = $query
        ->condition($field_name, $field_value, '=')
        ->range(0, $limit)
        ->execute();
      $entities = $storage->loadMultiple($entity_ids);
    }

    // Set provided value.
    // @todo Ensure that the provided context has the correct entity type.
    $this->setProvidedValue('entity_fetched', $entities);
  }

}
