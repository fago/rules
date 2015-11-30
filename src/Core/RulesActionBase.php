<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesActionBase.
 */

namespace Drupal\rules\Core;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\rules\Context\ContextProviderTrait;
use Drupal\rules\Core\ConfigurationAccessControlTrait;

/**
 * Base class for rules actions.
 */
abstract class RulesActionBase extends ContextAwarePluginBase implements RulesActionInterface {

  use ContextProviderTrait;
  use ExecutablePluginTrait;
  use ConfigurationAccessControlTrait;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions() {
    // Do not refine anything by default.
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
  public function executeMultiple(array $objects) {
    // @todo: Remove this once it is removed from the interface.
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    // Per default no context parameters will be auto saved.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // Just deny access per default for now.
    if ($return_as_object) {
      return AccessResult::forbidden();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Provide a reasonable default implementation that calls doExecute() while
    // passing the defined context as arguments.
    $args = [];
    foreach ($this->getContextDefinitions() as $name => $definition) {
      $args[$name] = $this->getContextValue($name);
    }
    call_user_func_array([$this, 'doExecute'], $args);
  }

}
