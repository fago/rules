<?php

namespace Drupal\rules\Engine;

use Drupal\rules\Context\ContextConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Common base class for action and condition expression containers.
 */
abstract class ExpressionContainerBase extends ExpressionBase implements ExpressionContainerInterface {

  /**
   * The expression manager.
   *
   * @var \Drupal\rules\Engine\ExpressionManagerInterface
   */
  protected $expressionManager;

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
  public function addExpression($plugin_id, ContextConfig $config = NULL) {
    return $this->addExpressionObject(
      $this->expressionManager->createInstance($plugin_id, $config ? $config->toArray() : [])
    );
  }

  /**
   * Determines whether child-expressions are allowed to assert metadata.
   *
   * @return bool
   *   Whether child-expressions are allowed to assert metadata.
   *
   * @see \Drupal\rules\Engine\ExpressionInterface::prepareExecutionMetadataState()
   */
  abstract protected function allowsMetadataAssertions();

  /**
   * {@inheritdoc}
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state, $apply_assertions = TRUE) {
    $violation_list = new IntegrityViolationList();
    $this->prepareExecutionMetadataStateBeforeTraversal($metadata_state);
    $apply_assertions = $apply_assertions && $this->allowsMetadataAssertions();
    foreach ($this as $child_expression) {
      $child_violations = $child_expression->checkIntegrity($metadata_state, $apply_assertions);
      $violation_list->addAll($child_violations);
    }
    $this->prepareExecutionMetadataStateAfterTraversal($metadata_state);
    return $violation_list;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareExecutionMetadataState(ExecutionMetadataStateInterface $metadata_state, ExpressionInterface $until = NULL, $apply_assertions = TRUE) {
    if ($until && $this->getUuid() === $until->getUuid()) {
      return TRUE;
    }
    $this->prepareExecutionMetadataStateBeforeTraversal($metadata_state);
    $apply_assertions = $apply_assertions && $this->allowsMetadataAssertions();
    foreach ($this as $child_expression) {
      $found = $child_expression->prepareExecutionMetadataState($metadata_state, $until, $apply_assertions);
      // If the expression was found, we need to stop.
      if ($found) {
        return TRUE;
      }
    }
    $this->prepareExecutionMetadataStateAfterTraversal($metadata_state);
  }

  /**
   * Prepares execution metadata state before traversing through children.
   *
   * @see ::prepareExecutionMetadataState()
   * @see ::checkIntegrity()
   */
  protected function prepareExecutionMetadataStateBeforeTraversal(ExecutionMetadataStateInterface $metadata_state) {
    // Any pre-traversal preparations need to be added here.
  }

  /**
   * Prepares execution metadata state after traversing through children.
   *
   * @see ::prepareExecutionMetadataState()
   * @see ::checkIntegrity()
   */
  protected function prepareExecutionMetadataStateAfterTraversal(ExecutionMetadataStateInterface $metadata_state) {
    // Any post-traversal preparations need to be added here.
  }

}
