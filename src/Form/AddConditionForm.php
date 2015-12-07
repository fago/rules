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
use Drupal\rules\Entity\ReactionRuleStorage;
use Drupal\rules\Plugin\RulesExpression\ReactionRule;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI form for adding a Rules condition.
 */
class AddConditionForm extends FormBase {

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\rules\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The reaction rule storage to load form.
   *
   * @var ReactionRuleStorage
   */
  protected $ruleStorage;

  /**
   * The reaction rule this form is used for.
   *
   * @var string
   */
  protected $reactionRuleId;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ConditionManager $condition_manager, ReactionRuleStorage $rules_storage) {
    $this->conditionManager = $condition_manager;
    $this->ruleStorage = $rules_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.condition'),
      $container->get('entity_type.manager')->getStorage('rules_reaction_rule')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRule $rules_reaction_rule = NULL) {
    // @todo why is the reaction rule not passed upcasted? Why is it even
    // possible that it is a string here?
    $this->reactionRuleId = $rules_reaction_rule;
    $condition_name = $form_state->getValue('condition');

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

    /** @var \Drupal\rules\Core\RulesConditionInterface $condition */
    $condition = $this->conditionManager->createInstance($condition_name);

    // Step 2 of the form.
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
    return 'rules_reaction_condition_add';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'save') {
      $reaction_rule = $this->ruleStorage->load($this->reactionRuleId);
      $expression = $reaction_rule->getExpression();

      $context_config = ContextConfig::create();
      foreach ($form_state->getValue('context') as $context_name => $value) {
        $context_config->setValue($context_name, $value);
      }

      $expression->addCondition($form_state->getValue('condition'), $context_config);
      // Set the expression again so that the config is copied over to the
      // config entity.
      $reaction_rule->setExpression($expression);
      $reaction_rule->save();

      $form_state->setRedirect('entity.rules_reaction_rule.edit_form', [
        'rules_reaction_rule' => $reaction_rule->id(),
      ]);
    }
    else {
      $form_state->setRebuild();
    }
  }

}
