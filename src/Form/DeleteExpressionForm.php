<?php

/**
 * @file
 * Contains \Drupal\rules\Form\DeleteExpressionForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Entity\ReactionRuleConfig;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Removes an expression from a rule.
 */
class DeleteExpressionForm extends ConfirmFormBase {

  use TempStoreTrait;

  /**
   * The reaction rule config the expression is deleted from.
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
  public function getFormId() {
    return 'rules_delete_expression';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ReactionRuleConfig $rules_reaction_rule = NULL, $uuid = NULL) {
    $this->ruleConfig = $rules_reaction_rule;
    $this->uuid = $uuid;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $rule_expression = $this->ruleConfig->getExpression();
    $expression_inside = $rule_expression->getExpression($this->uuid);
    if (!$expression_inside) {
      throw new NotFoundHttpException();
    }

    return $this->t('Are you sure you want to delete %title from %rule?', [
      '%title' => $expression_inside->getLabel(),
      '%rule' => $this->ruleConfig->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->ruleConfig->urlInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $expression = $this->ruleConfig->getExpression();
    $expression->deleteExpression($this->uuid);
    // Set the expression again so that the config is copied over to the
    // config entity.
    $this->ruleConfig->setExpression($expression);

    $this->saveToTempStore();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
