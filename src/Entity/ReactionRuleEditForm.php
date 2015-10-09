<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\ReactionRuleEditForm.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to edit a reaction rule.
 */
class ReactionRuleEditForm extends RulesComponentFormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('Reaction rule %label has been updated.', ['%label' => $this->entity->label()]));
  }

}
