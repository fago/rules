<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExpressionBase
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\rules\Context\ContextProviderTrait;

/**
 * Base class for rules actions.
 */
abstract class ExpressionBase extends ContextAwarePluginBase implements ExpressionInterface {

  use ContextProviderTrait;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Overrides the parent constructor to populate context definitions.
   *
   * Expression plugins can be configured to have arbitrary context definitions.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context_definitions' may
   *   be used to initialize the context definitions by setting it to an array
   *   of definitions keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (isset($configuration['context_definitions'])) {
      $plugin_definition['context'] = $this->createContextDefinitions($configuration['context_definitions']);
    }
    if (isset($configuration['provided_definitions'])) {
      $plugin_definition['provides'] = $this->createContextDefinitions($configuration['provided_definitions']);
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /* Converts a context definition configuration array into an object.
   *
   * @todo This should be replaced by some convenience method on the
   *   ContextDefinition class in core?
   *
   * @param array $configuration
   *   The configuration properties for populating the context definition
   *   object.
   *
   * @return \Drupal\Core\Plugin\Context\ContextDefinitionInterface[]
   *   A list of context definitions keyed by the context name.
   */
  protected function createContextDefinitions(array $configuration) {
    $context_definitions = [];
    foreach ($configuration as $context_name => $definition_array) {
      $definition_array += [
        'type' => 'any',
        'label' => NULL,
        'required' => TRUE,
        'multiple' => FALSE,
        'description' => NULL,
      ];

      $context_definitions[$context_name] = new ContextDefinition(
        $definition_array['type'], $definition_array['label'],
        $definition_array['required'], $definition_array['multiple'],
        $definition_array['description']
      );
    }
    return $context_definitions;
  }

  /**
   * Executes a rules expression.
   */
  public function execute() {
    $contexts = $this->getContexts();
    $state = new RulesState($contexts);
    $result = $this->executeWithState($state);
    // Save specifically registered variables in the end after execution.
    $state->autoSave();
    return $result;
  }

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

}
