<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextHandlerTrait.
 */

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;
use Drupal\rules\Engine\ExecutionMetadataStateInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Exception\RulesEvaluationException;

/**
 * Provides methods for handling context based on the plugin configuration.
 *
 * The trait requires the plugin to use configuration as defined by the
 * ContextConfig class.
 *
 * @see \Drupal\rules\Context\ContextConfig
 */
trait ContextHandlerTrait {

  /**
   * The data processor plugin manager used to process context variables.
   *
   * @var \Drupal\rules\Context\DataProcessorManager
   */
  protected $processorManager;

  /**
   * Maps variables from the execution state into the plugin context.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin that is populated with context values.
   * @param \Drupal\rules\Engine\ExecutionStateInterface $state
   *   The Rules state containing available variables.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   In case a required context is missing for the plugin.
   */
  protected function mapContext(CoreContextAwarePluginInterface $plugin, ExecutionStateInterface $state) {
    $context_definitions = $plugin->getContextDefinitions();
    foreach ($context_definitions as $name => $definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name])) {
        $typed_data = $state->fetchDataByPropertyPath($this->configuration['context_mapping'][$name]);

        if ($typed_data->getValue() === NULL && !$definition->isAllowedNull()) {
          throw new RulesEvaluationException('The value of data selector '
            . $this->configuration['context_mapping'][$name] . " is NULL, but the context $name in "
            . $plugin->getPluginId() . ' requires a value.');
        }
        $context = $plugin->getContext($name);
        $new_context = Context::createFromContext($context, $typed_data);
        $plugin->setContext($name, $new_context);
      }
      elseif (isset($this->configuration['context_values'])
        && array_key_exists($name, $this->configuration['context_values'])
      ) {

        if ($this->configuration['context_values'][$name] === NULL && !$definition->isAllowedNull()) {
          throw new RulesEvaluationException("The context value for $name is NULL, but the context $name in "
            . $plugin->getPluginId() . ' requires a value.');
        }

        $context = $plugin->getContext($name);
        $new_context = Context::createFromContext($context, $this->configuration['context_values'][$name]);
        $plugin->setContext($name, $new_context);
      }
      elseif ($definition->isRequired()) {
        throw new RulesEvaluationException("Required context $name is missing for plugin "
          . $plugin->getPluginId() . '.');
      }
    }
  }

  /**
   * Gets the definition of the data that is mapped to the given context.
   *
   * @param string $context_name
   *   The name of the context.
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The metadata state containing metadata about available variables.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|null
   *   A data definition if the property path could be applied, or NULL if the
   *   context is not mapped.
   *
   * @throws \Drupal\rules\Exception\RulesIntegrityException
   *   Thrown if the data selector that is configured for the context is
   *   invalid.
   */
  protected function getMappedDefinition($context_name, ExecutionMetadataStateInterface $metadata_state) {
    if (isset($this->configuration['context_mapping'][$context_name])) {
      return $metadata_state->fetchDefinitionByPropertyPath($this->configuration['context_mapping'][$context_name]);
    }
  }

  /**
   * Adds provided context values from the plugin to the execution state.
   *
   * @param CoreContextAwarePluginInterface $plugin
   *   The context aware plugin of which to add provided context.
   * @param \Drupal\rules\Engine\ExecutionStateInterface $state
   *   The Rules state where the context variables are added.
   */
  protected function addProvidedContext(CoreContextAwarePluginInterface $plugin, ExecutionStateInterface $state) {
    // If the plugin does not support providing context, there is nothing to do.
    if (!$plugin instanceof ContextProviderInterface) {
      return;
    }
    $provides = $plugin->getProvidedContextDefinitions();
    foreach ($provides as $name => $provided_definition) {
      // Avoid name collisions in the rules state: provided variables can be
      // renamed.
      if (isset($this->configuration['provides_mapping'][$name])) {
        $state->setVariableData($this->configuration['provides_mapping'][$name], $plugin->getProvidedContext($name)->getContextData());
      }
      else {
        $state->setVariableData($name, $plugin->getProvidedContext($name)->getContextData());
      }
    }
  }

  /**
   * Adds the definitions of provided context to the execution metadata state.
   *
   * @param CoreContextAwarePluginInterface $plugin
   *   The context aware plugin of which to add provided context.
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The execution metadata state to add variables to.
   */
  protected function addProvidedContextDefinitions(CoreContextAwarePluginInterface $plugin, ExecutionMetadataStateInterface $metadata_state) {
    // If the plugin does not support providing context, there is nothing to do.
    if (!$plugin instanceof ContextProviderInterface) {
      return;
    }

    foreach ($plugin->getProvidedContextDefinitions() as $name => $context_definition) {
      if (isset($this->configuration['provides_mapping'][$name])) {
        // Populate the state with the new variable that is provided by this
        // plugin. That is necessary so that the integrity check in subsequent
        // actions knows about the variable and does not throw violations.
        $metadata_state->setDataDefinition(
          $this->configuration['provides_mapping'][$name],
          $context_definition->getDataDefinition()
        );
      }
      else {
        $metadata_state->setDataDefinition($name, $context_definition->getDataDefinition());
      }
    }
  }

  /**
   * Process data context on the plugin, usually before it gets executed.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin to process the context data on.
   * @param \Drupal\rules\Engine\ExecutionStateInterface $rules_state
   *   The current Rules execution state with context variables.
   */
  protected function processData(CoreContextAwarePluginInterface $plugin, ExecutionStateInterface $rules_state) {
    if (isset($this->configuration['context_processors'])) {
      foreach ($this->configuration['context_processors'] as $context_name => $processors) {
        $value = $plugin->getContextValue($context_name);
        foreach ($processors as $processor_plugin_id => $configuration) {
          $data_processor = $this->processorManager->createInstance($processor_plugin_id, $configuration);
          $value = $data_processor->process($value, $rules_state);
        }
        $plugin->setContextValue($context_name, $value);
      }
    }
  }

}
