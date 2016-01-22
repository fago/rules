<?php

/**
 * @file
 * Contains \Drupal\rules\Form\EditExpressionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Entity\ReactionRuleConfig;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * UI form to edit an expression like a condition or action in a rule.
 */
class EditExpressionForm extends FormBase {

  use TempStoreTrait {
    validateForm as lockValidateForm;
  }

  /**
   * The reaction rule config the expression is edited on.
   *
   * @var \Drupal\rules\Entity\ReactionRuleConfig
   */
  protected $ruleConfig;

  /**
   * The UUID of the expression in the rule.
   *
   * @var string
   */
  protected $uuid;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRuleConfig $reaction_config = NULL, $uuid = NULL) {
    $this->ruleConfig = $reaction_config;
    $this->uuid = $uuid;

    $rule_expression = $reaction_config->getExpression();
    $expression_inside = $rule_expression->getExpression($uuid);
    if (!$expression_inside) {
      throw new NotFoundHttpException();
    }
    $form_handler = $expression_inside->getFormHandler();
    $form = $form_handler->form($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_expression_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->lockValidateForm($form, $form_state);

    // In order to validdate the whole rule we need to invoke the submission
    // handler of the expression form. That way the expression is changed and we
    // can validate the change for integrity afterwards.
    $validation_config = clone $this->ruleConfig;
    $rule_expression = $validation_config->getExpression();
    $expression = $rule_expression->getExpression($this->uuid);
    $form_handler = $expression->getFormHandler();
    $form_handler->submitForm($form, $form_state);

    $all_violations = RulesComponent::create($rule_expression)
      ->addContextDefinitionsFrom($validation_config)
      ->checkIntegrity();
    $local_violations = $all_violations->getFor($this->uuid);

    foreach ($local_violations as $violation) {
      $form_state->setError($form, $violation->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $rule_expression = $this->ruleConfig->getExpression();
    $expression = $rule_expression->getExpression($this->uuid);
    $form_handler = $expression->getFormHandler();
    $form_handler->submitForm($form, $form_state);

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
  public function getTitle(ReactionRuleConfig $reaction_config, $uuid) {
    $rule_expression = $reaction_config->getExpression();
    $expression_inside = $rule_expression->getExpression($uuid);
    return $this->t('Edit @expression', ['@expression' => $expression_inside->getLabel()]);
  }

}
