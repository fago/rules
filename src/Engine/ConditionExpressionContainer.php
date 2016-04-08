<?php

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Exception\InvalidExpressionException;

/**
 * Container for conditions.
 */
abstract class ConditionExpressionContainer extends ExpressionContainerBase implements ConditionExpressionContainerInterface, ContainerFactoryPluginInterface {

  /**
   * List of conditions that are evaluated.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionInterface[]
   */
  protected $conditions = [];

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
    $this->expressionManager = $expression_manager;

    $configuration += ['conditions' => []];
    foreach ($configuration['conditions'] as $condition_config) {
      $condition = $this->expressionManager->createInstance($condition_config['id'], $condition_config);
      $this->conditions[] = $condition;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addExpressionObject(ExpressionInterface $expression) {
    if (!$expression instanceof ConditionExpressionInterface) {
      throw new InvalidExpressionException('Only condition expressions can be added to a condition container.');
    }
    if ($this->getExpression($expression->getUuid())) {
      throw new InvalidExpressionException('A condition with the same UUID already exists in the container.');
    }
    $this->conditions[] = $expression;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition($condition_id, ContextConfig $config = NULL) {
    return $this->addExpressionObject(
      $this->expressionManager
        ->createCondition($condition_id)
        ->setConfiguration($config ? $config->toArray() : [])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(ExecutionStateInterface $rules_state) {
    $result = $this->evaluate($rules_state);
    return $this->isNegated() ? !$result : $result;
  }

  /**
   * Returns the final result after executing the conditions.
   */
  abstract public function evaluate(ExecutionStateInterface $rules_state);

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isNegated() {
    return !empty($this->configuration['negate']);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $configuration = parent::getConfiguration();
    // We need to update the configuration in case conditions have been added or
    // changed.
    $configuration['conditions'] = [];
    foreach ($this->conditions as $condition) {
      $configuration['conditions'][] = $condition->getConfiguration();
    }
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator($this->conditions);
  }

  /**
   * PHP magic __clone function.
   */
  public function __clone() {
    // Implement a deep clone.
    foreach ($this->conditions as &$condition) {
      $condition = clone $condition;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExpression($uuid) {
    foreach ($this->conditions as $condition) {
      if ($condition->getUuid() === $uuid) {
        return $condition;
      }
      if ($condition instanceof ExpressionContainerInterface) {
        $nested_condition = $condition->getExpression($uuid);
        if ($nested_condition) {
          return $nested_condition;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteExpression($uuid) {
    foreach ($this->conditions as $index => $condition) {
      if ($condition->getUuid() === $uuid) {
        unset($this->conditions[$index]);
        return TRUE;
      }
      if ($condition instanceof ExpressionContainerInterface
        && $condition->deleteExpression($uuid)
      ) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
