<?php

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Exception\InvalidExpressionException;

/**
 * Container for actions.
 */
abstract class ActionExpressionContainer extends ExpressionContainerBase implements ActionExpressionContainerInterface, ContainerFactoryPluginInterface {

  /**
   * List of actions that will be executed.
   *
   * @var \Drupal\rules\Engine\ActionExpressionInterface[]
   */
  protected $actions = [];

  /**
   * Constructor.
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
    $this->expressionManager = $expression_manager;

    $configuration += ['actions' => []];
    foreach ($configuration['actions'] as $action_config) {
      $action = $expression_manager->createInstance($action_config['id'], $action_config);
      $this->actions[] = $action;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addExpressionObject(ExpressionInterface $expression) {
    if (!$expression instanceof ActionExpressionInterface) {
      throw new InvalidExpressionException('Only action expressions can be added to an action container.');
    }
    if ($this->getExpression($expression->getUuid())) {
      throw new InvalidExpressionException('An action with the same UUID already exists in the container.');
    }
    $this->actions[] = $expression;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addAction($action_id, ContextConfig $config = NULL) {
    return $this->addExpressionObject(
      $this->expressionManager
        ->createAction($action_id)
        ->setConfiguration($config ? $config->toArray() : [])
    );
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

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->actions);
  }

  /**
   * PHP magic __clone function.
   */
  public function __clone() {
    // Implement a deep clone.
    foreach ($this->actions as &$action) {
      $action = clone $action;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExpression($uuid) {
    foreach ($this->actions as $action) {
      if ($action->getUuid() === $uuid) {
        return $action;
      }
      if ($action instanceof ExpressionContainerInterface) {
        $nested_action = $action->getExpression($uuid);
        if ($nested_action) {
          return $nested_action;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteExpression($uuid) {
    foreach ($this->actions as $index => $action) {
      if ($action->getUuid() === $uuid) {
        unset($this->actions[$index]);
        return TRUE;
      }
      if ($action instanceof ExpressionContainerInterface
        && $action->deleteExpression($uuid)
      ) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
