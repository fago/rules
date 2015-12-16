<?php

/**
 * @file
 * Contains \Drupal\rules\Form\AddActionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Core\RulesActionManagerInterface;
use Drupal\rules\Entity\ReactionRuleConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI form for adding a Rules action.
 */
class AddActionForm extends FormBase {

  /**
   * The action plugin manager.
   *
   * @var \Drupal\rules\Core\RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * Creates a new object of this class.
   */
  public function __construct(RulesActionManagerInterface $action_manager) {
    $this->actionManager = $action_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.rules_action'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRuleConfig $reaction_config = NULL) {
    $form_state->set('reaction_config', $reaction_config);
    $action_name = $form_state->getValue('action');

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
        '#empty_value' => $this->t('- Select -'),
      ];
      $form['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#name' => 'continue',
      ];

      return $form;
    }

    $action = $this->actionManager->createInstance($action_name);

    // Step 2 of the form.
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
      $form['context'][$context_name] = [
        '#type' => 'textfield',
        '#title' => $context_definition->getLabel(),
        '#description' => $context_definition->getDescription(),
        '#required' => $context_definition->isRequired(),
      ];
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
  public function getFormId() {
    return 'rules_reaction_action_add';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'save') {
      $reaction_config = $form_state->get('reaction_config');
      $expression = $reaction_config->getExpression();

      $context_config = ContextConfig::create();
      foreach ($form_state->getValue('context') as $context_name => $value) {
        $context_config->setValue($context_name, $value);
      }

      $expression->addAction($form_state->getValue('action'), $context_config);
      // Set the expression again so that the config is copied over to the
      // config entity.
      $reaction_config->setExpression($expression);
      $reaction_config->save();

      drupal_set_message($this->t('Your changes have been saved.'));

      $form_state->setRedirect('entity.rules_reaction_rule.edit_form', [
        'rules_reaction_rule' => $reaction_config->id(),
      ]);
    }
    else {
      $form_state->setRebuild();
    }
  }

}
