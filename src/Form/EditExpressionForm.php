<?php

/**
 * @file
 * Contains \Drupal\rules\Form\EditExpressionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Entity\ReactionRuleConfig;

/**
 * UI form to edit an expression like a condition or action in a rule.
 */
class EditExpressionForm extends FormBase {

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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $rule_expression = $this->ruleConfig->getExpression();
    $expression = $rule_expression->getExpression($this->uuid);
    $form_handler = $expression->getFormHandler();
    $form_handler->submitForm($form, $form_state);

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
  public function getTitle(ReactionRuleConfig $reaction_config, $uuid) {
    $rule_expression = $reaction_config->getExpression();
    $expression_inside = $rule_expression->getExpression($uuid);
    return $this->t('Edit @expression', ['@expression' => $expression_inside->getLabel()]);
  }

}
