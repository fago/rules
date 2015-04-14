<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\EntityFetchByField.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Fetch entities by field' action.
 *
 * @Action(
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
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityFetchByField object.
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
    return $this->t('Fetch entities by field');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Retrieve context values for action.
    $entity_type = $this->getContextValue('type');
    $field_name = $this->getContextValue('field_name');
    $field_value = $this->getContextValue('field_value');
    $limit = $this->getContextValue('limit');

    $storage = $this->entityManager->getStorage($entity_type);

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
