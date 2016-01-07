<?php

/**
 * @file
 * Contains \Drupal\rules\Form\AddExpressionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Drupal\rules\Entity\ReactionRuleConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI form to add an expression like a condition or action to a rule.
 */
class AddExpressionForm extends FormBase {

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
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRuleConfig $reaction_config = NULL, $expression_id = NULL) {
    $this->ruleConfig = $reaction_config;
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $expression = $this->expressionManager->createInstance($this->expressionId);
    $form_handler = $expression->getFormHandler();
    $form_handler->submitForm($form, $form_state);

    $rule_expression = $this->ruleConfig->getExpression();
    $rule_expression->addExpressionObject($expression);
    // Set the expression again so that the config is copied over to the
    // config entity.
    $this->ruleConfig->setExpression($rule_expression);
    $this->ruleConfig->save();

    drupal_set_message($this->t('Your changes have been saved.'));

    $form_state->setRedirect('entity.rules_reaction_rule.edit_form', [
      'rules_reaction_rule' => $this->ruleConfig->id(),
    ]);
  }

  /**
   * Provides the page title on the form.
   */
  public function getTitle(ReactionRuleConfig $reaction_config, $expression_id) {
    $expression = $this->expressionManager->createInstance($expression_id);
    return $this->t('Add @expression', ['@expression' => $expression->getLabel()]);
  }

}
