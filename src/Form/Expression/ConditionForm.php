<?php

/**
 * @file
 * Contains \Drupal\rules\Form\Expression\ConditionForm.
 */

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rules\Core\ConditionManager;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Engine\ConditionExpressionInterface;
use Drupal\rules\Form\Expression\ExpressionFormInterface;

/**
 * UI form for adding/editing a Rules condition.
 */
class ConditionForm implements ExpressionFormInterface {

  use ContextFormTrait;
  use StringTranslationTrait;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\rules\Core\ConditionManager
   */
  protected $conditionManager;

  /**
   * The condition expression that is edited in the form.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionInterface
   */
  protected $conditionExpression;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ConditionExpressionInterface $condition_expression, ConditionManager $condition_manager) {
    $this->conditionManager = $condition_manager;
    $this->conditionExpression = $condition_expression;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $condition_name = $form_state->get('condition');
    $configuration = $this->conditionExpression->getConfiguration();
    if (empty($condition_name) && !empty($configuration['condition_id'])) {
      $condition_name = $configuration['condition_id'];
    }

    // Step 1 of the multistep form.
    if (!$condition_name) {
      $condition_definitions = $this->conditionManager->getGroupedDefinitions();
      $options = [];
      foreach ($condition_definitions as $group => $definitions) {
        foreach ($definitions as $id => $definition) {
          $options[$group][$id] = $definition['label'];
        }
      }

      $form['condition'] = [
        '#type' => 'select',
        '#title' => $this->t('Condition'),
        '#options' => $options,
        '#required' => TRUE,
      ];
      $form['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#name' => 'continue',
        // Only validate the selected condition in the first step.
        '#limit_validation_errors' => [['condition']],
        '#submit' => [static::class . '::submitFirstStep'],
      ];

      return $form;
    }

    // Step 2 of the form.
    /** @var \Drupal\rules\Core\RulesConditionInterface $condition */
    $condition = $this->conditionManager->createInstance($condition_name);

    $form['summary'] = [
      '#markup' => $condition->summary(),
    ];
    $form['condition'] = [
      '#type' => 'value',
      '#value' => $condition_name,
    ];

    $context_definitions = $condition->getContextDefinitions();

    $form['context']['#tree'] = TRUE;
    foreach ($context_definitions as $context_name => $context_definition) {
      $form = $this->buildContextForm($form, $form_state, $context_name, $context_definition, $configuration);
    }

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#name' => 'save',
    ];

    return $form;
  }

  /**
   * Submit callback: save the selected condition in the first step.
   */
  public function submitFirstStep(array &$form, FormStateInterface $form_state) {
    $form_state->set('condition', $form_state->getValue('condition'));
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $context_config = ContextConfig::create();
    foreach ($form_state->getValue('context') as $context_name => $value) {
      if ($form_state->get("context_$context_name") == 'selector') {
        $context_config->map($context_name, $value['setting']);
      }
      else {
        $context_config->setValue($context_name, $value['setting']);
      }
    }

    $configuration = $context_config->toArray();
    $configuration['condition_id'] = $form_state->getValue('condition');
    $this->conditionExpression->setConfiguration($configuration);
  }

}
