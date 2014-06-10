<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User has entity field access' condition.
 *
 * @Condition(
 *   id = "rules_entity_field_access",
 *   label = @Translation("User has entity field access")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class UserHasEntityFieldAccess extends RulesConditionBase {

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
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TypedDataManager $typed_data_manager, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $typed_data_manager);
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
      $container->get('typed_data_manager'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['entity'] = ContextDefinition::create($typed_data_manager, 'entity')
      ->setLabel(t('Entity'));

    $contexts['field'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Field name'));

    $contexts['op'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Operation'));

    $contexts['account'] = ContextDefinition::create($typed_data_manager, 'entity:user')
      ->setLabel(t('User'));

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('User has access to field on entity');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $entity = $this->getContextValue('entity');
    $field = $this->getContextValue('field');
    if (!$entity->hasField($field)) {
      return FALSE;
    }

    $op = $this->getContextValue('op');
    $account = $this->getContextValue('account');
    $access = $this->entityManager->getAccessController($entity->getEntityTypeId());
    if (!$access->access($entity, $op, Language::LANGCODE_DEFAULT, $account)) {
      return FALSE;
    }

    $definition = $entity->getFieldDefinition($field);
    $items = $entity->get($field);
    return $access->fieldAccess($op, $definition, $account, $items);
  }
}
