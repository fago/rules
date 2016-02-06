<?php

/**
 * @file
 * Contains \Drupal\rules\Form\AddExpressionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Entity\ReactionRuleConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI form to add an expression like a condition or action to a rule.
 */
class AddExpressionForm extends FormBase {

  use TempStoreTrait {
    validateForm as lockValidateForm;
  }

  /**
   * The Rules expression manager to get expression plugins.
   *
   * @var ExpressionManagerInterface
   */
  protected $expressionManager;

  /**
   * The reaction rule config the expression is added to.
   *
   * @var \Drupal\rules\Entity\ReactionRuleConfig
   */
  protected $ruleConfig;

  /**
   * The expression ID that is added, example: 'rules_action'.
   *
   * @var string
   */
  protected $expressionId;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ExpressionManagerInterface $expression_manager) {
    $this->expressionManager = $expression_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.rules_expression'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRuleConfig $rules_reaction_rule = NULL, $expression_id = NULL) {
    $this->ruleConfig = $rules_reaction_rule;
    $this->expressionId = $expression_id;

    $expression = $this->expressionManager->createInstance($expression_id);
    $form_handler = $expression->getFormHandler();
    $form = $form_handler->form($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_expression_add';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->lockValidateForm($form, $form_state);

    $expression = $this->expressionManager->createInstance($this->expressionId);
    $form_handler = $expression->getFormHandler();
    $form_handler->validateForm($form, $form_state);

    $validation_config = clone $this->ruleConfig;
    $rule_expression = $validation_config->getExpression();
    $rule_expression->addExpressionObject($expression);

    $all_violations = RulesComponent::create($rule_expression)
      ->addContextDefinitionsFrom($validation_config)
      ->checkIntegrity();
    $local_violations = $all_violations->getFor($expression->getUuid());

    foreach ($local_violations as $violation) {
      $form_state->setError($form, $violation->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $expression = $this->expressionManager->createInstance($this->expressionId);
    $form_handler = $expression->getFormHandler();
    $form_handler->submitForm($form, $form_state);

    $rule_expression = $this->ruleConfig->getExpression();
    $rule_expression->addExpressionObject($expression);
    // Set the expression again so that the config is copied over to the
    // config entity.
    $this->ruleConfig->setExpression($rule_expression);

    $this->saveToTempStore();

    $form_state->setRedirect('entity.rules_reaction_rule.edit_form', [
      'rules_reaction_rule' => $this->ruleConfig->id(),
    ]);
  }

  /**
   * Provides the page title on the form.
   */
  public function getTitle(ReactionRuleConfig $rules_reaction_rule, $expression_id) {
    $expression = $this->expressionManager->createInstance($expression_id);
    return $this->t('Add @expression', ['@expression' => $expression->getLabel()]);
  }

}
