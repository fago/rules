<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\EntitySave.
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
class EntitySave extends RulesActionBase {

  /**
   * Flag that indicates if the entity should be auto-saved later.
   *
   * @var bool
   */
  protected $saveLater = TRUE;

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Save entity');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // We only need to do something here if the immediate flag is set, otherwise
    // the entity will be auto-saved after the execution.
    if ((bool) $this->getContextValue('immediate')) {
      $entity = $this->getContextValue('entity');
      $entity->save();
      $this->saveLater = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    if ($this->saveLater) {
      return ['entity'];
    }
    return [];
  }

}
