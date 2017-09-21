<?php

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Engine\ActionExpressionContainerInterface;
use Drupal\rules\Engine\ActionExpressionInterface;
use Drupal\rules\Engine\ConditionExpressionContainerInterface;
use Drupal\rules\Engine\ConditionExpressionInterface;
use Drupal\rules\Engine\ExecutionMetadataStateInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Engine\ExpressionBase;
use Drupal\rules\Engine\ExpressionInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Drupal\rules\Exception\InvalidExpressionException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a rule, executing actions when conditions are met.
 *
 * Actions added to a rule can also be rules themselves, so it is possible to
 * nest several rules into one rule. This is the functionality of so called
 * "rule sets" in Drupal 7.
 *
 * @RulesExpression(
 *   id = "rules_rule",
 *   label = @Translation("Rule"),
 *   form_class = "\Drupal\rules\Form\Expression\RuleForm"
 * )
 */
class Rule extends ExpressionBase implements RuleInterface, ContainerFactoryPluginInterface {

  /**
   * List of conditions that must be met before actions are executed.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionContainerInterface
   */
  protected $conditions;

  /**
   * List of actions that get executed if the conditions are met.
   *
   * @var \Drupal\rules\Engine\ActionExpressionContainerInterface
   */
  protected $actions;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Engine\ExpressionManagerInterface $expression_manager
   *   The rules expression plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ExpressionManagerInterface $expression_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $configuration += ['conditions' => [], 'actions' => []];
    // Per default the outer condition container of a rule is initialized as
    // conjunction (AND), meaning that all conditions in it must evaluate to
    // TRUE to fire the actions.
    $this->conditions = $expression_manager->createInstance('rules_and', $configuration['conditions']);
    $this->conditions->setRoot($this->getRoot());
    $this->actions = $expression_manager->createInstance('rules_action_set', $configuration['actions']);
    $this->actions->setRoot($this->getRoot());
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.rules_expression')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(ExecutionStateInterface $state) {
    // Evaluate the rule's conditions.
    if (!$this->conditions->isEmpty() && !$this->conditions->executeWithState($state)) {
      // Do not run the actions if the conditions are not met.
      return;
    }
    $this->actions->executeWithState($state);
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition($condition_id, ContextConfig $config = NULL) {
    return $this->conditions->addCondition($condition_id, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    return $this->conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions(ConditionExpressionContainerInterface $conditions) {
    $this->conditions = $conditions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addAction($action_id, ContextConfig $config = NULL) {
    return $this->actions->addAction($action_id, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function getActions() {
    return $this->actions;
  }

  /**
   * {@inheritdoc}
   */
  public function setActions(ActionExpressionContainerInterface $actions) {
    $this->actions = $actions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addExpressionObject(ExpressionInterface $expression) {
    if ($expression instanceof ConditionExpressionInterface) {
      $this->conditions->addExpressionObject($expression);
    }
    elseif ($expression instanceof ActionExpressionInterface) {
      $this->actions->addExpressionObject($expression);
    }
    else {
      throw new InvalidExpressionException();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addExpression($plugin_id, ContextConfig $config = NULL) {
    return $this->addExpressionObject(
      $this->expressionManager->createInstance($plugin_id, $config ? $config->toArray() : [])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $configuration = parent::getConfiguration();
    // We need to update the configuration in case actions/conditions have been
    // added or changed.
    $configuration['conditions'] = $this->conditions->getConfiguration();
    $configuration['actions'] = $this->actions->getConfiguration();
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    // Just pass up the actions for iterating over.
    return $this->actions->getIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function getExpression($uuid) {
    $condition = $this->conditions->getExpression($uuid);
    if ($condition) {
      return $condition;
    }
    return $this->actions->getExpression($uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteExpression($uuid) {
    $deleted = $this->conditions->deleteExpression($uuid);
    if (!$deleted) {
      $deleted = $this->actions->deleteExpression($uuid);
    }
    return $deleted;
  }

  /**
   * {@inheritdoc}
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state, $apply_assertions = TRUE) {
    $violation_list = $this->conditions->checkIntegrity($metadata_state, $apply_assertions);
    $violation_list->addAll($this->actions->checkIntegrity($metadata_state, $apply_assertions));
    return $violation_list;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareExecutionMetadataState(ExecutionMetadataStateInterface $metadata_state, ExpressionInterface $until = NULL, $apply_assertions = TRUE) {
    // @todo: If the rule is nested, we may not pass assertions to following
    // expressions as we do not know whether the rule fires at all. Should we
    // clone the metadata state to ensure modifications stay local?
    $found = $this->conditions->prepareExecutionMetadataState($metadata_state, $until, $apply_assertions);
    if ($found) {
      return TRUE;
    }
    return $this->actions->prepareExecutionMetadataState($metadata_state, $until, $apply_assertions);
  }

  /**
   * PHP magic __clone function.
   */
  public function __clone() {
    $this->actions = clone $this->actions;
    $this->actions->setRoot($this->getRoot());
    $this->conditions = clone $this->conditions;
    $this->conditions->setRoot($this->getRoot());
  }

}
