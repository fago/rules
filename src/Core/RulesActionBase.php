<?php

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\rules\Context\ContextProviderTrait;

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
  public function getContextValue($name) {
    try {
      return parent::getContextValue($name);
    }
    catch (ContextException $e) {
      // Catch the undocumented exception thrown when no context value is set
      // for a required context.
      // @todo: Remove once https://www.drupal.org/node/2677162 is fixed.
      if (strpos($e->getMessage(), 'context is required') === FALSE) {
        throw $e;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions(array $selected_data) {
    // Do not refine anything by default.
  }

  /**
   * {@inheritdoc}
   */
  public function assertMetadata(array $selected_data) {
    // Nothing to assert by default.
    return [];
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
