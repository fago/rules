<?php

/**
 * @file
 * Contains \Drupal\rules\Form\Expression\ExpressionFormInterface.
 */

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
   * @param FormStateInterface $form_state
   *   The current form state.
   */
  public function form(array $form, FormStateInterface $form_state);

}
