<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\NodeIsPromoted.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Entity has field' condition.
 *
 * @Condition(
 *   id = "rules_entity_has_field",
 *   label = @Translation("Entity has field")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class EntityHasField extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['entity'] = ContextDefinition::create($typed_data_manager, 'entity')
      ->setLabel(t('Entity'))
      ->setDescription(t('Specifies the entity for which to evaluate the condition.'));

    $contexts['field'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Field'))
      ->setDescription(t('The name of the field to check for.'))
      ->setRequired(TRUE);

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Entity has field');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $entity = $this->getContextValue('entity');
    $field = $this->getContextValue('field');
    return $entity->hasField($field);
  }

}
