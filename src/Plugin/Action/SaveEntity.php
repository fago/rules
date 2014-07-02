<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\SaveEntity.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Save entity' action.
 *
 * @Action(
 *   id = "rules_entity_save",
 *   label = @Translation("Save entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity, which should be saved permanently.")
 *     ),
 *     "immediate" = @ContextDefinition("boolean",
 *       label = @Translation("Force saving immediately"),
 *       description = @Translation("Usually saving is postponed till the end of the evaluation, so that multiple saves can be fold into one. If this set, saving is forced to happen immediately."),
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class SaveEntity extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Save entity.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // @todo Properly implement this.
    if (!$immediate = (bool) $this->getContextValue('immediate')) {
      return;
    }

    $entity = $this->getContextValue('entity');
    $entity->save();
  }

}
