<?php

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides base methods for expression forms.
 */
trait ExpressionFormTrait {

  /**
   * Implements ExpressionFormInterface::submitForm().
   *
   * Empty default implementation.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Implements ExpressionFormInterface::validateForm().
   *
   * Empty default implementation.
   */
  public function validateForm(array $form, FormStateInterface $form_state) {
  }

}
