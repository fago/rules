<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\EntityInterface;
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
 *       description = @Translation("Specifies the entity for which to evaluate the condition."),
 *       assignment_restriction = "selector",
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
   * Check if a provided entity is of a specific type and bundle.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check the bundle and type of.
   * @param string $type
   *   The type to check for.
   * @param string $bundle
   *   The bundle to check for.
   *
   * @return bool
   *   TRUE if the provided entity is of the provided type and bundle.
   */
  protected function doEvaluate(EntityInterface $entity, $type, $bundle) {
    $entity_type = $entity->getEntityTypeId();
    $entity_bundle = $entity->bundle();

    // Check to see whether the entity's bundle and type match the specified
    // values.
    return $entity_bundle == $bundle && $entity_type == $type;
  }

  /**
   * {@inheritdoc}
   */
  public function assertMetadata(array $selected_data) {
    // Assert the checked bundle.
    $changed_definitions = [];
    if (isset($selected_data['entity']) && $bundle = $this->getContextValue('bundle')) {
      $changed_definitions['entity'] = clone $selected_data['entity'];
      $bundles = is_array($bundle) ? $bundle : [$bundle];
      $changed_definitions['entity']->setBundles($bundles);
    }
    return $changed_definitions;
  }

}
