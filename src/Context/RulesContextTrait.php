<?php

/**
 * @file
 * Contains \Drupal\rules\Context\RulesContextTrait.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\Context\Context;
use Drupal\rules\Engine\RulesState;

/**
 * Offers common methods for context plugin implementors.
 */
trait RulesContextTrait {

  /**
   * The data objects that are provided by this plugin.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $provided;

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function setProvidedValue($name, $value) {
    $this->getProvided($name)->setContextValue($value);
    return $this;
  }

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function getProvided($name) {
    // Check for a valid context value.
    if (!isset($this->provided[$name])) {
      $this->provided[$name] = new Context($this->getProvidedDefinition($name));
    }
    return $this->provided[$name];
  }

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function getProvidedDefinition($name) {
    $definition = $this->getPluginDefinition();
    if (empty($definition['provides'][$name])) {
      throw new ContextException(sprintf("The %s provided context is not valid.", $name));
    }
    return $definition['provides'][$name];
  }

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function getProvidedDefinitions() {
    $definition = $this->getPluginDefinition();
    return !empty($definition['provides']) ? $definition['provides'] : array();
  }

  /**
   * Maps variables from rules state into the plugin context.
   *
   * @param \Drupal\rules\Context\ContextAwarePluginInterface $plugin
   *   The plugin that is populated with context values.
   * @param \Drupal\rules\Context\RulesState $state
   *   The Rules state containing available variables.
   */
  protected function mapContext(ContextAwarePluginInterface $plugin, RulesState $state) {
    $context_definitions = $plugin->getContextDefinitions();
    foreach ($context_definitions as $name => $definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name . ':select'])) {
        $typed_data = $state->applyDataSelector($this->configuration['context_mapping'][$name . ':select']);
        $plugin->setContextValue($name, $typed_data);
      }
      else {
        // Check if the state has a variable with the same name.
        $state_variable = $state->getVariable($name);
        if ($state_variable) {
          $plugin->setContext($name, $state_variable);
        }
      }
      // @todo check if the context is required.
    }
  }

  /**
   * Maps provided context values from the plugin to the Rules state.
   *
   * @param ProvidedContextPluginInterface $plugin
   *   The plugin where the context values are extracted.
   * @param \Drupal\rules\Context\RulesState $state
   *   The Rules state where the context variables are added.
   */
  protected function mapProvidedContext(ProvidedContextPluginInterface $plugin, RulesState $state) {
    $provides = $plugin->getProvidedDefinitions();
    foreach ($provides as $name => $provided_definition) {

      // Avoid name collisions in the rules state: provided variables can be
      // renamed.
      if (isset($this->configuration['provides_mapping'][$name])) {
        $state->addVariable($this->configuration['provides_mapping'][$name], $plugin->getProvided($name));
      }
      else {
        $state->addVariable($name, $plugin->getProvided($name));
      }
    }
  }

}
