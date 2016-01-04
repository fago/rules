<?php

/**
 * @file
 * Contains \Drupal\rules\Form\AddConditionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Condition\ConditionManager;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Entity\ReactionRuleConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI form for adding a Rules condition.
 */
class AddConditionForm extends FormBase {

  use ContextFormTrait;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\rules\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ConditionManager $condition_manager) {
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.condition'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRuleConfig $reaction_config = NULL) {
    $form_state->set('reaction_config', $reaction_config);
    $condition_name = $form_state->get('condition');

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
        '#empty_value' => $this->t('- Select -'),
      ];
      $form['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#name' => 'continue',
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

    $context_defintions = $condition->getContextDefinitions();

    $form['context']['#tree'] = TRUE;
    foreach ($context_defintions as $context_name => $context_definition) {
      $form = $this->buildContextForm($form, $form_state, $context_name, $context_definition);
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
    return 'rules_reaction_condition_add';
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
        if ($form_state->get("context_$context_name") == 'selector') {
          $context_config->map($context_name, $value['setting']);
        }
        else {
          $context_config->setValue($context_name, $value['setting']);
        }
      }

      $expression->addCondition($form_state->getValue('condition'), $context_config);
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
      $form_state->set('condition', $form_state->getValue('condition'));
      $form_state->setRebuild();
    }
  }

}
