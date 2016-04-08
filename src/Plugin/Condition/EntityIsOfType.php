<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\EntityInterface;
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
   * Check if the provided entity is of a specific type.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check for a type.
   * @param string $type
   *   The type to check for.
   *
   * @return bool
   *   TRUE if the entity is of the provided type.
   */
  protected function doEvaluate(EntityInterface $entity, $type) {
    $entity_type = $entity->getEntityTypeId();

    // Check to see whether the entity's type matches the specified value.
    return $entity_type == $type;
  }

}
