<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\Rule.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Engine\RulesConditionContainerInterface;
use Drupal\rules\Engine\RulesConditionInterface;
use Drupal\rules\Engine\RulesExpressionInterface;
use Drupal\rules\Plugin\RulesExpressionPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a rule, executing actions when conditions are met.
 *
 * @RulesExpression(
 *   id = "rules_rule",
 *   label = @Translation("A rule, executing actions when conditions are met.")
 * )
 */
class Rule extends PluginBase implements RuleInterface, RulesExpressionInterface, ContainerFactoryPluginInterface {

  /**
   * List of conditions that must be met before actions are executed.
   *
   * @var \Drupal\rules\Engine\RulesConditionContainerInterface
   */
  protected $conditions;

  /**
   * List of actions that get executed if the conditions are met.
   *
   * @var \Drupal\Core\Action\ActionInterface[]
   */
  protected $actions = [];

  /**
   * Constructs a Rule object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Plugin\RulesExpressionPluginManager $expression_manager
   *   The rules expression plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RulesExpressionPluginManager $expression_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Per default the outer condition container of a rule is initialized as
    // conjunction (AND), meaning that all conditions in it must evaluate to
    // TRUE to fire the actions.
    $this->conditions = $expression_manager->createInstance('rules_and');
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
  public function execute() {
    // Evaluate the rule's conditions.
    if (!$this->conditions->execute()) {
      // Do not run the actions if the conditions are not met.
      return;
    }
    foreach ($this->actions as $action) {
      $action->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition(RulesConditionInterface $condition) {
    $this->conditions->addCondition($condition);
    return $this;
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
  public function setConditions(RulesConditionContainerInterface $conditions) {
    $this->conditions = $conditions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addAction(ActionInterface $action) {
    $this->actions[] = $action;
    return $this;
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
  public function executeMultiple(array $objects) {
    // @todo: Implement.
  }

}
