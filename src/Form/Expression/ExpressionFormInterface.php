<?php

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines methods for expression forms.
 */
interface ExpressionFormInterface {

  /**
   * Adds elements specific to the expression to the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function form(array $form, FormStateInterface $form_state);

  /**
   * Form validation callback to validate expression elements.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateForm(array $form, FormStateInterface $form_state);

  /**
   * Form submission callback to save changes for the expression.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state);

}
