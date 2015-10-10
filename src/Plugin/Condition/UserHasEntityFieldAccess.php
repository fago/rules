<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User has entity field access' condition.
 *
 * @Condition(
 *   id = "rules_entity_field_access",
 *   label = @Translation("User has entity field access"),
 *   category = @Translation("User"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Field")
 *     ),
 *     "operation" = @ContextDefinition("string",
 *       label = @Translation("Operation")
 *     ),
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class UserHasEntityFieldAccess extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a UserHasEntityFieldAccess object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
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
   * Evaluate if the user has access to the field of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check access on.
   * @param string $field
   *   The name of the field to check access on.
   * @param string $operation
   *   The operation access should be checked for. Usually one of "view" or
   *   "edit".
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user account to test access against.
   *
   * @return bool
   *   TRUE if the user has access to the field on the entity, FALSE otherwise.
   */
  protected function doEvaluate(ContentEntityInterface $entity, $field, $operation, AccountInterface $user) {
    if (!$entity->hasField($field)) {
      return FALSE;
    }

    $access = $this->entityManager->getAccessControlHandler($entity->getEntityTypeId());
    if (!$access->access($entity, $operation, $user)) {
      return FALSE;
    }

    $definition = $entity->getFieldDefinition($field);
    $items = $entity->get($field);
    return $access->fieldAccess($operation, $definition, $user, $items);
  }

}
