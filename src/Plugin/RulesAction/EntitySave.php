<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Save entity' action.
 *
 * @RulesAction(
 *   id = "rules_entity_save",
 *   label = @Translation("Save entity"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity, which should be saved permanently.")
 *     ),
 *     "immediate" = @ContextDefinition("boolean",
 *       label = @Translation("Force saving immediately"),
 *       description = @Translation("Usually saving is postponed till the end of the evaluation, so that multiple saves can be fold into one. If this set, saving is forced to happen immediately."),
 *       default_value = FALSE,
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntitySave extends RulesActionBase {

  /**
   * Flag that indicates if the entity should be auto-saved later.
   *
   * @var bool
   */
  protected $saveLater = TRUE;

  /**
   * Saves the Entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be saved.
   * @param bool $immediate
   *   (optional) Save the entity immediately.
   */
  protected function doExecute(EntityInterface $entity, $immediate) {
    // We only need to do something here if the immediate flag is set, otherwise
    // the entity will be auto-saved after the execution.
    if ((bool) $immediate) {
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
