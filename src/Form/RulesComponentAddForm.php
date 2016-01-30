<?php

/**
 * @file
 * Contains \Drupal\rules\Form\RulesComponentAddForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to add a component.
 */
class RulesComponentAddForm extends RulesComponentFormBase {

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

    drupal_set_message($this->t('Component %label has been created.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.rules_component.edit_form', ['rules_component' => $this->entity->id()]);
  }

}
