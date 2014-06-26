<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesConditionBase.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\ContextAwarePluginBase;

/**
 * Base class for rules conditions.
 */
abstract class RulesConditionBase extends ContextAwarePluginBase implements RulesConditionInterface {

  /**
   * The condition manager to proxy execute calls through.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $executableManager;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The data objects that are provided by this condition.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $provided;

  /**
   * {@inheritdoc}
   */
  public function setExecutableManager(ExecutableManagerInterface $executableManager) {
    $this->executableManager = $executableManager;
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
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this->executableManager->execute($this);
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
  public function setProvidedValue($name, $value) {
    $this->getProvided($name)->setContextValue($value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProvided($name) {
    // Check for a valid context value.
    if (!isset($this->provided[$name])) {
      $this->provided[$name] = new Context($this->getProvidedDefinition($name));
    }
    return $this->provided[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getProvidedDefinition($name) {
    $definition = $this->getPluginDefinition();
    if (empty($definition['provides'][$name])) {
      throw new ContextException(sprintf("The %s provided context is not valid.", $name));
    }
    return $definition['provides'][$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getProvidedDefinitions() {
    $definition = $this->getPluginDefinition();
    return !empty($definition['provides']) ? $definition['provides'] : array();
  }

}
