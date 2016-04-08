<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Ui\RulesUiHandlerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Removes an expression from a rule.
 */
class DeleteExpressionForm extends ConfirmFormBase {

  /**
   * The UUID of the expression in the rule.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The RulesUI handler of the currently active UI.
   *
   * @var \Drupal\rules\Ui\RulesUiHandlerInterface
   */
  protected $rulesUiHandler;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_delete_expression';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RulesUiHandlerInterface $rules_ui_handler = NULL, $uuid = NULL) {
    $this->rulesUiHandler = $rules_ui_handler;
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
    $rule_expression = $this->rulesUiHandler->getComponent()->getExpression();
    $expression_inside = $rule_expression->getExpression($this->uuid);
    if (!$expression_inside) {
      throw new NotFoundHttpException();
    }

    return $this->t('Are you sure you want to delete %title from %rule?', [
      '%title' => $expression_inside->getLabel(),
      '%rule' => $this->rulesUiHandler->getComponentLabel(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->rulesUiHandler->getBaseRouteUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $component = $this->rulesUiHandler->getComponent();
    $component->getExpression()->deleteExpression($this->uuid);
    $this->rulesUiHandler->updateComponent($component);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
