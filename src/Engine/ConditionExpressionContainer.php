<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ConditionExpressionContainer.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Exception\InvalidExpressionException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container for conditions.
 */
abstract class ConditionExpressionContainer extends ExpressionBase implements ConditionExpressionContainerInterface, ContainerFactoryPluginInterface {

  /**
   * List of conditions that are evaluated.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface[]
   */
  protected $conditions = [];

  /**
   * The UUID generating service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

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
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID generating service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ExpressionManagerInterface $expression_manager, UuidInterface $uuid_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->expressionManager = $expression_manager;
    $this->uuidService = $uuid_service;

    $configuration += ['conditions' => []];
    foreach ($configuration['conditions'] as $condition_config) {
      $condition = $this->expressionManager->createInstance($condition_config['id'], $condition_config);
      $this->addExpressionObject($condition);
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
      $container->get('plugin.manager.rules_expression'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addExpressionObject(ExpressionInterface $expression) {
    if (!$expression instanceof ConditionExpressionInterface) {
      throw new InvalidExpressionException();
    }
    $this->conditions[$this->uuidService->generate()] = $expression;
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
    foreach ($this->conditions as $uuid => $condition) {
      $configuration['conditions'][$uuid] = $condition->getConfiguration();
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
   * {@inheritdoc}
   */
  public function getExpression($uuid) {
    if (isset($this->conditions[$uuid])) {
      return $this->conditions[$uuid];
    }
    foreach ($this->conditions as $condition) {
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
    if (isset($this->conditions[$uuid])) {
      unset($this->conditions[$uuid]);
      return TRUE;
    }
    foreach ($this->conditions as $condition) {
      if ($condition instanceof ExpressionContainerInterface
        && $condition->deleteExpression($uuid)
      ) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
