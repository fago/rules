<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\EntityIsOfBundle.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides an 'Entity is of bundle' condition.
 *
 * @Condition(
 *   id = "rules_entity_is_of_bundle",
 *   label = @Translation("Entity is of bundle"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition.")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Type"),
 *       description = @Translation("The type of the evaluated entity.")
 *     ),
 *     "bundle" = @ContextDefinition("string",
 *       label = @Translation("Bundle"),
 *       description = @Translation("The bundle of the evaluated entity.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 */
class EntityIsOfBundle extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $provided_entity = $this->getContextValue('entity');
    $specified_type = $this->getContextValue('type');
    $specified_bundle = $this->getContextValue('bundle');
    $entity_type = $provided_entity->getEntityTypeId();
    $entity_bundle = $provided_entity->bundle();

    // Check to see whether the entity's bundle and type match the specified
    // values.
    return $entity_bundle == $specified_bundle && $entity_type == $specified_type;
  }

}
