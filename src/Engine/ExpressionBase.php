<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExpressionBase.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for rules expressions.
 */
abstract class ExpressionBase extends PluginBase implements ExpressionInterface {

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The root expression if this object is nested.
   *
   * @var \Drupal\rules\Engine\ExpressionInterface
   */
  protected $root;

  /**
   * The config entity this expression is associated with, if any.
   *
   * @var string
   */
  protected $configEntityId;

  /**
   * Executes a rules expression.
   */
  public function execute() {
    // If there is no state given, we have to assume no required context.
    $state = ExecutionState::create();
    $result = $this->executeWithState($state);
    // Save specifically registered variables in the end after execution.
    $state->autoSave();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'id' => $this->getPluginId(),
    ] + $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormHandler() {
    if (isset($this->pluginDefinition['form_class'])) {
      $class_name = $this->pluginDefinition['form_class'];
      return new $class_name($this);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRoot() {
    if (isset($this->root)) {
      return $this->root->getRoot();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRoot(ExpressionInterface $root) {
    $this->root = $root;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigEntityId() {
    return $this->configEntityId;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigEntityId($id) {
    $this->configEntityId = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

}
