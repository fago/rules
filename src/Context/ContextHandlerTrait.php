<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextHandlerTrait.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;
use Drupal\rules\Engine\RulesStateInterface;
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
   * Maps variables from rules state into the plugin context.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin that is populated with context values.
   * @param \Drupal\rules\Engine\RulesStateInterface $state
   *   The Rules state containing available variables.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   In case a required context is missing for the plugin.
   */
  protected function mapContext(CoreContextAwarePluginInterface $plugin, RulesStateInterface $state) {
    $context_definitions = $plugin->getContextDefinitions();
    foreach ($context_definitions as $name => $definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name])) {
        $typed_data = $state->applyDataSelector($this->configuration['context_mapping'][$name]);

        if ($typed_data->getValue() === NULL && !$definition->isAllowedNull()) {
          throw new RulesEvaluationException(SafeMarkup::format('The value of data selector @selector is NULL, but the context @name in @plugin requires a value.', [
            '@selector' => $this->configuration['context_mapping'][$name],
            '@name' => $name,
            '@plugin' => $plugin->getPluginId(),
          ]));
        }
        $context = $plugin->getContext($name);
        $new_context = Context::createFromContext($context, $typed_data);
        $plugin->setContext($name, $new_context);
      }
      elseif (isset($this->configuration['context_values'])
        && array_key_exists($name, $this->configuration['context_values'])
      ) {

        if ($this->configuration['context_values'][$name] === NULL && !$definition->isAllowedNull()) {
          throw new RulesEvaluationException(SafeMarkup::format('The context value for @name is NULL, but the context @name in @plugin requires a value.', [
            '@name' => $name,
            '@plugin' => $plugin->getPluginId(),
          ]));
        }

        $context = $plugin->getContext($name);
        $new_context = Context::createFromContext($context, $this->configuration['context_values'][$name]);
        $plugin->setContext($name, $new_context);
      }
      elseif ($definition->isRequired()) {
        throw new RulesEvaluationException(SafeMarkup::format('Required context @name is missing for plugin @plugin.', [
          '@name' => $name,
          '@plugin' => $plugin->getPluginId(),
        ]));
      }
    }
  }

  /**
   * Maps provided context values from the plugin to the Rules state.
   *
   * @param ContextProviderInterface $plugin
   *   The plugin where the context values are extracted.
   * @param \Drupal\rules\Engine\RulesStateInterface $state
   *   The Rules state where the context variables are added.
   */
  protected function mapProvidedContext(ContextProviderInterface $plugin, RulesStateInterface $state) {
    $provides = $plugin->getProvidedContextDefinitions();
    foreach ($provides as $name => $provided_definition) {
      // Avoid name collisions in the rules state: provided variables can be
      // renamed.
      if (isset($this->configuration['provides_mapping'][$name])) {
        $state->addVariable($this->configuration['provides_mapping'][$name], $plugin->getProvidedContext($name)->getContextData());
      }
      else {
        $state->addVariable($name, $plugin->getProvidedContext($name)->getContextData());
      }
    }
  }

  /**
   * Process data context on the plugin, usually before it gets executed.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin to process the context data on.
   * @param \Drupal\rules\Engine\RulesStateInterface $rules_state
   *   The current Rules execution state with context variables.
   */
  protected function processData(CoreContextAwarePluginInterface $plugin, RulesStateInterface $rules_state) {
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
