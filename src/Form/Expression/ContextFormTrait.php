<?php

/**
 * @file
 * Contains \Drupal\rules\Form\Expression\ContextFormTrait.
 */

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Context\ContextDefinitionInterface;

/**
 * Provides form logic for handling contexts when configuring an expression.
 */
trait ContextFormTrait {

  /**
   * Provides the form part for a context parameter.
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, $context_name, ContextDefinitionInterface $context_definition, array $configuration) {
    $form['context'][$context_name] = [
      '#type' => 'fieldset',
      '#title' => $context_definition->getLabel(),
    ];
    $form['context'][$context_name]['description'] = [
      '#markup' => $context_definition->getDescription(),
    ];

    // If the form has been submitted already take the mode from the submitted
    // values, otherwise default to existing configuration. And if that does not
    // exist default to the "input" mode.
    $mode = $form_state->get('context_' . $context_name);
    if (!$mode) {
      if (isset($configuration['context_mapping'][$context_name])) {
        $mode = 'selector';
      }
      else {
        $mode = 'input';
      }
      $form_state->set('context_' . $context_name, $mode);
    }

    $title = $mode == 'selector' ? $this->t('Data selector') : $this->t('Value');
    // @todo get a description for possible values that can be filled in.
    $description = $mode == 'selector'
      ? $this->t("The data selector helps you drill down into the data available to Rules. <em>To make entity fields appear in the data selector, you may have to use the condition 'entity has field' (or 'content is of type').</em> More useful tips about data selection is available in <a href=':url'>the online documentation</a>.", [
        ':url' => 'https://www.drupal.org/node/1300042',
      ]) : '';

    if (isset($configuration['context_values'][$context_name])) {
      $default_value = $configuration['context_values'][$context_name];
    }
    elseif (isset($configuration['context_mapping'][$context_name])) {
      $default_value = $configuration['context_mapping'][$context_name];
    }
    else {
      $default_value = $context_definition->getDefaultValue();
    }
    $form['context'][$context_name]['setting'] = [
      '#type' => 'textfield',
      '#title' => $title,
      '#description' => $description,
      '#required' => $context_definition->isRequired(),
      '#default_value' => $default_value,
    ];

    $value = $mode == 'selector' ? $this->t('Switch to the direct input mode') : $this->t('Switch to data selection');
    $form['context'][$context_name]['switch_button'] = [
      '#type' => 'submit',
      '#name' => 'context_' . $context_name,
      '#attributes' => ['class' => ['rules-switch-button']],
      '#parameter' => $context_name,
      '#value' => $value,
      '#submit' => [static::class . '::switchContextMode'],
      // Do not validate!
      '#limit_validation_errors' => [],
    ];
    return $form;
  }

  /**
   * Submit callback: switch a context to data selecor or direct input mode.
   */
  public static function switchContextMode(array &$form, FormStateInterface $form_state) {
    $element_name = $form_state->getTriggeringElement()['#name'];
    $mode = $form_state->get($element_name);
    $switched_mode = $mode == 'selector' ? 'input' : 'selector';
    $form_state->set($element_name, $switched_mode);

    $form_state->setRebuild();
  }

}
