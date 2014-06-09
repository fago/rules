<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\EntityIsOfType.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Entity is of type' condition.
 *
 * @Condition(
 *   id = "rules_entity_is_of_type",
 *   label = @Translation("Entity is of type")
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 * @todo: Add group information from Drupal 7?
 */
class EntityIsOfType extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('typed_data_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['entity'] = ContextDefinition::create($typed_data_manager, 'entity')
      ->setLabel(t('Entity'))
      ->setDescription(t('Specifies the entity for which to evaluate the condition.'));

    $contexts['type'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Type'))
      ->setDescription(t('The entity type specified by the condition.'));

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Entity is of type');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $provided_entity = $this->getContextValue('entity');
    $specified_type = $this->getContextValue('type');
    $entity_type = $provided_entity->getEntityTypeId();

    // Check to see whether the entity's type matches the specified value.
    return $entity_type == $specified_type;
  }
}
