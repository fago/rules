<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextHandlerTrait.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Component\Utility\String;
use Drupal\rules\Exception\RulesEvaluationException;
use Drupal\rules\Engine\RulesState;

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
   * @param \Drupal\Component\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin that is populated with context values.
   * @param \Drupal\rules\Engine\RulesState $state
   *   The Rules state containing available variables.
   *
   * @throws \Drupal\rules\Exception\RulesEvaluationException
   *   In case a required context is missing for the plugin.
   */
  protected function mapContext(ContextAwarePluginInterface $plugin, RulesState $state) {
    $context_definitions = $plugin->getContextDefinitions();
    foreach ($context_definitions as $name => $definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name])) {
        $typed_data = $state->applyDataSelector($this->configuration['context_mapping'][$name]);
        $plugin->getContext($name)->setContextData($typed_data);
      }
      // @todo: This misses support for picking up pre-defined values here.

      elseif ($definition->isRequired()) {
        throw new RulesEvaluationException(String::format('Required context @name is missing for plugin @plugin.', [
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
   * @param \Drupal\rules\Engine\RulesState $state
   *   The Rules state where the context variables are added.
   */
  protected function mapProvidedContext(ContextProviderInterface $plugin, RulesState $state) {
    $provides = $plugin->getProvidedContextDefinitions();
    foreach ($provides as $name => $provided_definition) {
      // Avoid name collisions in the rules state: provided variables can be
      // renamed.
      if (isset($this->configuration['provides_mapping'][$name])) {
        $state->addVariable($this->configuration['provides_mapping'][$name], $plugin->getProvidedContext($name));
      }
      else {
        $state->addVariable($name, $plugin->getProvidedContext($name));
      }
    }
  }

  /**
   * Process data context on the plugin, usually before it gets executed.
   *
   * @param \Drupal\Component\Plugin\ContextAwarePluginInterface $plugin
   *   The plugin to process the context data on.
   */
  protected function processData(ContextAwarePluginInterface $plugin) {
    if (isset($this->configuration['context_processors'])) {
      foreach ($this->configuration['context_processors'] as $name => $settings) {
        $data_processor = $this->processorManager->createInstance($settings['plugin'], $settings['configuration']);
        $new_value = $data_processor->process($plugin->getContextValue($name));
        $plugin->setContextValue($name, $new_value);
      }
    }
  }

}
