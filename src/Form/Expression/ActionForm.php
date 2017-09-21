<?php

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rules\Core\RulesActionManagerInterface;
use Drupal\rules\Engine\ActionExpressionInterface;
use Drupal\rules\Ui\RulesUiHandlerTrait;

/**
 * UI form for adding/editing a Rules action.
 */
class ActionForm implements ExpressionFormInterface {

  use ContextFormTrait;
  use StringTranslationTrait;
  use RulesUiHandlerTrait;

  /**
   * The action plugin manager.
   *
   * @var \Drupal\rules\Core\RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * The action expression that is edited in the form.
   *
   * @var \Drupal\rules\Engine\ActionExpressionInterface
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
    $action_id = $form_state->get('action_id');
    $configuration = $this->actionExpression->getConfiguration();
    if (empty($action_id) && !empty($configuration['action_id'])) {
      $action_id = $configuration['action_id'];
      $form_state->set('action_id', $action_id);
    }

    // Step 1 of the multistep form.
    if (!$action_id) {
      $action_definitions = $this->actionManager->getGroupedDefinitions();
      $options = [];
      foreach ($action_definitions as $group => $definitions) {
        foreach ($definitions as $id => $definition) {
          $options[$group][$id] = $definition['label'];
        }
      }

      $form['action_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Action'),
        '#options' => $options,
        '#required' => TRUE,
      ];
      $form['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#name' => 'continue',
        // Only validate the selected action in the first step.
        '#limit_validation_errors' => [['action_id']],
        '#submit' => [static::class . '::submitFirstStep'],
      ];

      return $form;
    }

    // Step 2 of the form.
    $action = $this->actionManager->createInstance($action_id);

    $form['summary'] = [
      '#markup' => $action->summary(),
    ];

    $context_definitions = $action->getContextDefinitions();

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
   * {@inheritdoc}
   */
  public function validateForm(array $form, FormStateInterface $form_state) {
    // Only if there is an action selected already we can validate something.
    if ($form_state->get('action_id')) {
      // Invoke the submission handler which will setup the expression being
      // edited in the form. That way the expression is ready for other
      // validation handlers.
      $this->submitForm($form, $form_state);
    }
  }

  /**
   * Submit callback: save the selected action in the first step.
   */
  public static function submitFirstStep(array &$form, FormStateInterface $form_state) {
    $form_state->set('action_id', $form_state->getValue('action_id'));
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $action_id = $form_state->get('action_id');
    // Nothing todo as long as the first step is not completed.
    if (!$action_id) {
      return;
    }

    $action_definition = $this->actionManager->getDefinition($action_id);
    $context_config = $this->getContextConfigFromFormValues($form_state, $action_definition['context']);

    $configuration = $context_config->toArray();
    $configuration['action_id'] = $action_id;
    $this->actionExpression->setConfiguration($configuration);
  }

}
