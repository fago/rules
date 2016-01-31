<?php

/**
 * @file
 * Contains \Drupal\rules_test_ui_embed\Form\SettingsForm.
 */

namespace Drupal\rules_test_ui_embed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['rules_test_ui_embed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_test_ui_embed_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('rules_test_ui_embed.settings');

    $form['css_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS file'),
      '#default_value' => $config->get('css.0.file'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('rules_test_ui_embed.settings')
      ->set('css.0.file', $form_state->getValue('css_file'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
