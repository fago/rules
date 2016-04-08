<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Psr\Log\LogLevel;

/**
 * Provides rules settings form.
 */
class RulesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['rules.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rules.settings');
    $form['log'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('System log'),
    ];
    $form['log']['log'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log debug information'),
      '#default_value' => $config->get('log'),
    ];
    $form['log']['log_level_system'] = [
      '#type' => 'radios',
      '#title' => $this->t('Log level'),
      '#options' => [
        LogLevel::WARNING => $this->t('Log all warnings and errors'),
        LogLevel::ERROR => $this->t('Log errors only'),
      ],
      '#default_value' => $config->get('log_level_system') ? $config->get('log_level_system') : LogLevel::WARNING,
      '#description' => $this->t('Evaluations errors are logged to available loggers.'),
      '#states' => [
        // Hide the log_level radios when the debug log is disabled.
        'invisible' => [
          'input[name="log"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['debug_screen_log'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Screen log'),
    ];
    $form['debug_screen_log']['debug_screen'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show debug information on screen (in the HTML response)'),
      '#default_value' => $config->get('debug_screen'),
    ];
    $form['debug_screen_log']['log_level_screen'] = [
      '#type' => 'radios',
      '#title' => $this->t('Log level'),
      '#options' => [
        LogLevel::WARNING => $this->t('Log all warnings and errors'),
        LogLevel::ERROR => $this->t('Log errors only'),
      ],
      '#default_value' => $config->get('log_level_screen') ? $config->get('log_level_screen') : LogLevel::WARNING,
      '#description' => $this->t('Level of log messages shown on screen.'),
      '#states' => [
        // Hide the log_level radios when the debug log is disabled.
        'invisible' => [
          'input[name="debug_screen"]' => ['checked' => FALSE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('rules.settings')
      ->set('log', $form_state->getValue('log'))
      ->set('debug_screen', $form_state->getValue('debug_screen'))
      ->set('log_level_system', $form_state->getValue('log_level_system'))
      ->set('log_level_screen', $form_state->getValue('log_level_screen'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
