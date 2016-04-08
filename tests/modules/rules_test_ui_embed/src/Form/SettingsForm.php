<?php

namespace Drupal\rules_test_ui_embed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Ui\RulesUiConfigHandler;

/**
 * Implements the settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The RulesUI handler of the currently active UI.
   *
   * @var \Drupal\rules\Ui\RulesUiConfigHandler
   */
  protected $rulesUiHandler;

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
  protected function config($name) {
    // Get the editable config from the Rules UI handlers, such that any
    // interim changes to the contained Rules component are picked up.
    $config = $this->rulesUiHandler ? $this->rulesUiHandler->getConfig() : NULL;
    if ($config->getName() == $name && in_array($name, $this->getEditableConfigNames())) {
      return $config;
    }
    return parent::config($name);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RulesUiConfigHandler $rules_ui_handler = NULL) {
    $form = parent::buildForm($form, $form_state);
    $this->rulesUiHandler = $rules_ui_handler;
    $config = $this->config('rules_test_ui_embed.settings');

    $form['css_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS file'),
      '#default_value' => $config->get('css.0.file'),
      '#required' => TRUE,
    ];

    $form['conditions'] = $this->rulesUiHandler->getForm()
      ->buildForm([], $form_state);

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#limit_validation_errors' => [['locked']],
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancel'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->rulesUiHandler->getForm()
      ->validateForm($form['conditions'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->rulesUiHandler->getForm()
      ->submitForm($form['conditions'], $form_state);

    $this->config('rules_test_ui_embed.settings')
      ->set('css.0.file', $form_state->getValue('css_file'))
      ->save();

    // Also remove the temporarily stored component, it has been persisted now.
    $this->rulesUiHandler->clearTemporaryStorage();

    parent::submitForm($form, $form_state);
  }

  /**
   * Form submission handler for the 'cancel' action.
   */
  public function cancel(array $form, FormStateInterface $form_state) {
    $this->rulesUiHandler->clearTemporaryStorage();
    drupal_set_message($this->t('Canceled.'));
    $form_state->setRedirectUrl($this->rulesUiHandler->getBaseRouteUrl());
  }

}
