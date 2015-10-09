<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\EntityIsNew.
 */

namespace Drupal\rules\Plugin\Condition;

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
   * {@inheritdoc}
   */
  public function evaluate() {
    $provided_entity = $this->getContextValue('entity');
    return $provided_entity->isNew();
  }

}
