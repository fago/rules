<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\EntityDelete.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Delete entity' action.
 *
 * @RulesAction(
 *   id = "rules_entity_delete",
 *   label = @Translation("Delete entity"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity, which should be deleted permanently.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityDelete extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Delete entity');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $entity = $this->getContextValue('entity');
    $entity->delete();
  }

}
