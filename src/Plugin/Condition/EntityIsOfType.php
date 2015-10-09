<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\EntityIsOfType.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides an 'Entity is of type' condition.
 *
 * @Condition(
 *   id = "rules_entity_is_of_type",
 *   label = @Translation("Entity is of type"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition.")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Type"),
 *       description = @Translation("The entity type specified by the condition.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 */
class EntityIsOfType extends RulesConditionBase {

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
