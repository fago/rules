<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides an 'Entity is new' condition.
 *
 * @Condition(
 *   id = "rules_entity_is_new",
 *   label = @Translation("Entity is new"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 */
class EntityIsNew extends RulesConditionBase {

  /**
   * Check if the provided entity is new.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if the provided entity is new.
   */
  protected function doEvaluate(EntityInterface $entity) {
    return $entity->isNew();
  }

}
