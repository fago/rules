<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides a 'Entity has field' condition.
 *
 * @Condition(
 *   id = "rules_entity_has_field",
 *   label = @Translation("Entity has field"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition.")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Field"),
 *       description = @Translation("The name of the field to check for.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityHasField extends RulesConditionBase {

  /**
   * Checks if a given entity has a given field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to check for the provided field.
   * @param string $field
   *   The field to check for on the entity.
   *
   * @return bool
   *   TRUE if the provided entity has the provided field.
   */
  protected function doEvaluate(FieldableEntityInterface $entity, $field) {
    return $entity->hasField($field);
  }

}
