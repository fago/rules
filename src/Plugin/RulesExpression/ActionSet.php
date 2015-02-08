<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\ActionSet.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Engine\RulesActionBase;
use Drupal\rules\Engine\RulesActionContainerInterface;
use Drupal\rules\Engine\RulesExpressionActionInterface;
use Drupal\rules\Engine\RulesExpressionInterface;
use Drupal\rules\Engine\RulesExpressionTrait;
use Drupal\rules\Engine\RulesState;
use Drupal\rules\Exception\InvalidExpressionException;
use Drupal\rules\Plugin\RulesExpressionPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Holds a set of actions and executes all of them.
 *
 * @RulesExpression(
 *   id = "rules_action_set",
 *   label = @Translation("Action set")
 * )
 */
class ActionSet extends RulesActionBase implements RulesActionContainerInterface, ContainerFactoryPluginInterface {

  use RulesExpressionTrait;

  /**
   * List of actions that will be executed.
   *
   * @var \Drupal\rules\Engine\RulesExpressionActionInterface[]
   */
  protected $actions = [];

  /**
   * Constructs a new class instance.
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
    $this->expressionManager = $expression_manager;

    $configuration += ['actions' => []];
    foreach ($configuration['actions'] as $action_config) {
      $action = $expression_manager->createInstance($action_config['id'], $action_config);
      $this->addExpressionObject($action);
    }
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
  public function addExpressionObject(RulesExpressionInterface $expression) {
    if (!$expression instanceof RulesExpressionActionInterface) {
      throw new InvalidExpressionException();
    }
    $this->actions[] = $expression;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addExpression($plugin_id, $configuration = NULL) {
    return $this->addExpressionObject(
      $this->expressionManager->createInstance($plugin_id, $configuration ?: [])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addAction($action_id, $configuration = NULL) {
    return $this->addExpressionObject(
      $this->expressionManager
        ->createAction($action_id)
        ->setConfiguration($configuration ?: [])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(RulesState $state) {
    foreach ($this->actions as $action) {
      $action->executeWithState($state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $configuration = parent::getConfiguration();
    // We need to update the configuration in case actions have been added or
    // changed.
    $configuration['actions'] = [];
    foreach ($this->actions as $action) {
      $configuration['actions'][] = $action->getConfiguration();
    }
    return $configuration;
  }

}
