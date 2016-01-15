<?php

/**
 * @file
 * Contains \Drupal\rules\Form\Expression\ActionForm.
 */

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Core\RulesActionManagerInterface;
use Drupal\rules\Engine\ActionExpressionInterface;

/**
 * UI form for adding/editing a Rules action.
 */
class ActionForm implements ExpressionFormInterface {

  use ContextFormTrait;
  use StringTranslationTrait;

  /**
   * The action plugin manager.
   *
   * @var RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * The action expression that is edited in the form.
   *
   * @var ActionExpressionInterface
   */
  protected $actionExpression;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ActionExpressionInterface $action_expression, RulesActionManagerInterface $action_manager) {
    $this->actionManager = $action_manager;
    $this->actionExpression = $action_expression;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $action_name = $form_state->get('action');
    $configuration = $this->actionExpression->getConfiguration();
    if (empty($action_name) && !empty($configuration['action_id'])) {
      $action_name = $configuration['action_id'];
    }

    // Step 1 of the multistep form.
    if (!$action_name) {
      $action_definitions = $this->actionManager->getGroupedDefinitions();
      $options = [];
      foreach ($action_definitions as $group => $definitions) {
        foreach ($definitions as $id => $definition) {
          $options[$group][$id] = $definition['label'];
        }
      }

      $form['action'] = [
        '#type' => 'select',
        '#title' => $this->t('Action'),
        '#options' => $options,
        '#required' => TRUE,
      ];
      $form['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#name' => 'continue',
        '#submit' => [static::class . '::submitFirstStep'],
      ];

      return $form;
    }

    // Step 2 of the form.
    $action = $this->actionManager->createInstance($action_name);

    $form['summary'] = [
      '#markup' => $action->summary(),
    ];
    $form['action'] = [
      '#type' => 'value',
      '#value' => $action_name,
    ];

    $context_defintions = $action->getContextDefinitions();

    $form['context']['#tree'] = TRUE;
    foreach ($context_defintions as $context_name => $context_definition) {
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
   * Submit callback: save the selected action in the first step.
   */
  public function submitFirstStep(array &$form, FormStateInterface $form_state) {
    $form_state->set('action', $form_state->getValue('action'));
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
    $configuration['action_id'] = $form_state->getValue('action');
    $this->actionExpression->setConfiguration($configuration);
  }

}
