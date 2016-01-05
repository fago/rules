<?php

/**
 * @file
 * Contains \Drupal\rules\Form\RulesComponentEditForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to edit a component.
 */
class RulesComponentEditForm extends RulesComponentFormBase {

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
    drupal_set_message($this->t('Component %label has been updated.', ['%label' => $this->entity->label()]));
  }

  /**
   * Title callback: also display the rule label.
   */
  public function getTitle($rules_component) {
    return $this->t('Edit rules component "@label"', ['@label' => $rules_component->label()]);
  }

}
