<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ActionExpressionContainer.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Exception\InvalidExpressionException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container for actions.
 */
abstract class ActionExpressionContainer extends ExpressionBase implements ActionExpressionContainerInterface, ContainerFactoryPluginInterface {

  /**
   * List of actions that will be executed.
   *
   * @var \Drupal\rules\Engine\ActionExpressionInterface[]
   */
  protected $actions = [];

  /**
   * The UUID generating service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

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
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID generating service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExpressionManagerInterface $expression_manager, UuidInterface $uuid_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->expressionManager = $expression_manager;
    $this->uuidService = $uuid_service;

    $configuration += ['actions' => []];
    foreach ($configuration['actions'] as $uuid => $action_config) {
      $action = $expression_manager->createInstance($action_config['id'], $action_config);
      $this->actions[$uuid] = $action;
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
  public function addExpressionObject(ExpressionInterface $expression, $return_uuid = FALSE) {
    if (!$expression instanceof ActionExpressionInterface) {
      throw new InvalidExpressionException();
    }
    $uuid = $this->uuidService->generate();
    $this->actions[$uuid] = $expression;
    return $return_uuid ? $uuid : $this;
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
    foreach ($this->actions as $uuid => $action) {
      $configuration['actions'][$uuid] = $action->getConfiguration();
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
   * {@inheritdoc}
   */
  public function getExpression($uuid) {
    if (isset($this->actions[$uuid])) {
      return $this->actions[$uuid];
    }
    foreach ($this->actions as $action) {
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
    if (isset($this->actions[$uuid])) {
      unset($this->actions[$uuid]);
      return TRUE;
    }
    foreach ($this->actions as $action) {
      if ($action instanceof ExpressionContainerInterface
        && $action->deleteExpression($uuid)
      ) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state) {
    $violation_list = new IntegrityViolationList();
    foreach ($this->actions as $uuid => $action) {
      $action_violations = $action->checkIntegrity($metadata_state);
      foreach ($action_violations as $violation) {
        $violation->setUuid($uuid);
      }
      $violation_list->addAll($action_violations);
    }
    return $violation_list;
  }

}
