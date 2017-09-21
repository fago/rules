<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Ui\RulesUiHandlerInterface;
use Drupal\rules\Engine\ExpressionContainerInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Exception\LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * UI form to add an expression like a condition or action to a rule.
 */
class AddExpressionForm extends EditExpressionForm {

  /**
   * The Rules expression manager to get expression plugins.
   *
   * @var \Drupal\rules\Engine\ExpressionManagerInterface
   */
  protected $expressionManager;

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
  public function buildForm(array $form, FormStateInterface $form_state, RulesUiHandlerInterface $rules_ui_handler = NULL, $uuid = NULL, $expression_id = NULL) {
    $this->expressionId = $expression_id;
    $this->uuid = $uuid;

    // When initially adding the expression, we have to initialize the object
    // and add the expression.
    if (!$this->uuid) {
      // Before we add our edited expression to the component's expression,
      // we clone it such that we do not change the source component until
      // the form has been successfully submitted.
      $component = clone $rules_ui_handler->getComponent();
      $this->uuid = $this->getEditedExpression($component)->getUuid();
      $form_state->set('component', $component);
      $form_state->set('uuid', $this->uuid);
    }

    return parent::buildForm($form, $form_state, $rules_ui_handler, $this->uuid);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditedExpression(RulesComponent $component) {
    $component_expression = $component->getExpression();
    if (!$component_expression instanceof ExpressionContainerInterface) {
      throw new LogicException('Cannot add expression to expression of type ' . $component_expression->getPluginId());
    }
    if ($this->uuid && $expression = $component_expression->getExpression($this->uuid)) {
      return $expression;
    }
    else {
      $expression = $this->expressionManager->createInstance($this->expressionId);
      $rule_expression = $component->getExpression();
      $rule_expression->addExpressionObject($expression);
      return $expression;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $form_state->setRedirectUrl($this->rulesUiHandler->getBaseRouteUrl());
  }

  /**
   * Provides the page title on the form.
   */
  public function getTitle(RulesUiHandlerInterface $rules_ui_handler, $expression_id) {
    $this->expressionId = $expression_id;
    $expression = $this->expressionManager->createInstance($this->expressionId);
    return $this->t('Add @expression', ['@expression' => $expression->getLabel()]);
  }

}
