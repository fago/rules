<?php

namespace Drupal\rules\Engine;

use Drupal\Core\Entity\DependencyTrait;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Context\ContextDefinitionInterface;
use Drupal\rules\Exception\LogicException;

/**
 * Handles executable Rules components.
 */
class RulesComponent {

  use DependencyTrait;

  /**
   * The rules execution state.
   *
   * @var \Drupal\rules\Engine\ExecutionStateInterface
   */
  protected $state;

  /**
   * Definitions for the context used by the component.
   *
   * @var \Drupal\rules\Context\ContextDefinitionInterface[]
   */
  protected $contextDefinitions = [];

  /**
   * List of context names that is provided back to the caller.
   *
   * @var string[]
   */
  protected $providedContext = [];

  /**
   * The expression.
   *
   * @var \Drupal\rules\Engine\ExpressionInterface
   */
  protected $expression;

  /**
   * Constructs the object.
   *
   * @param \Drupal\rules\Engine\ExpressionInterface $expression
   *   The expression of the component.
   *
   * @return static
   */
  public static function create(ExpressionInterface $expression) {
    return new static($expression);
  }

  /**
   * Creates a component based on the given configuration array.
   *
   * @param array $configuration
   *   The component configuration, as returned from ::getConfiguration().
   *
   * @return static
   */
  public static function createFromConfiguration(array $configuration) {
    $configuration += [
      'context_definitions' => [],
      'provided_context_definitions' => [],
    ];
    // @todo: Can we improve this use dependency injection somehow?
    $expression_manager = \Drupal::service('plugin.manager.rules_expression');
    $expression = $expression_manager->createInstance($configuration['expression']['id'], $configuration['expression']);
    $component = static::create($expression);
    foreach ($configuration['context_definitions'] as $name => $definition) {
      $component->addContextDefinition($name, ContextDefinition::createFromArray($definition));
    }
    foreach ($configuration['provided_context_definitions'] as $name => $definition) {
      $component->provideContext($name);
    }
    return $component;
  }

  /**
   * Constructs the object.
   *
   * @param \Drupal\rules\Engine\ExpressionInterface $expression
   *   The expression of the component.
   */
  protected function __construct(ExpressionInterface $expression) {
    $this->state = ExecutionState::create();
    $this->expression = $expression;
  }

  /**
   * Gets the expression of the component.
   *
   * @return \Drupal\rules\Engine\ExpressionInterface
   *   The expression.
   */
  public function getExpression() {
    return $this->expression;
  }

  /**
   * Gets the configuration array of this component.
   *
   * @return array
   *   The configuration of this component. It contains the following keys:
   *   - expression: The configuration of the contained expression, including a
   *     nested 'id' key.
   *   - context_definitions: Array of context definition arrays, keyed by
   *     context name.
   *   - provided_context: The names of the context that is provided back.
   */
  public function getConfiguration() {
    return [
      'expression' => $this->expression->getConfiguration(),
      'context_definitions' => array_map(function (ContextDefinitionInterface $definition) {
        return $definition->toArray();
      }, $this->contextDefinitions),
      'provided_context_definitions' => $this->providedContext,
    ];
  }

  /**
   * Gets the execution state.
   *
   * @return \Drupal\rules\Engine\ExecutionStateInterface
   *   The execution state for this component.
   */
  public function getState() {
    return $this->state;
  }

  /**
   * Adds a context definition.
   *
   * @param string $name
   *   The name of the context to add.
   * @param \Drupal\rules\Context\ContextDefinitionInterface $definition
   *   The definition to add.
   *
   * @return $this
   */
  public function addContextDefinition($name, ContextDefinitionInterface $definition) {
    $this->contextDefinitions[$name] = $definition;
    return $this;
  }

  /**
   * Gets definitions for the context used by this component.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of context definitions, keyed by context name.
   */
  public function getContextDefinitions() {
    return $this->contextDefinitions;
  }

  /**
   * Adds the available event context for the given events.
   *
   * @param string[] $event_names
   *   The (fully qualified) event names; e.g., as configured for a reaction
   *   rule.
   *
   * @return $this
   */
  public function addContextDefinitionsForEvents(array $event_names) {
    foreach ($event_names as $event_name) {
      // @todo: Correctly handle multiple events to intersect available context.
      // @todo Use setter injection for the service.
      $event_definition = \Drupal::service('plugin.manager.rules_event')->getDefinition($event_name);
      foreach ($event_definition['context'] as $context_name => $context_definition) {
        $this->addContextDefinition($context_name, $context_definition);
      }
    }
    return $this;
  }

  /**
   * Marks the given context to be provided back to the caller.
   *
   * @param string $name
   *   The name of the context to provide.
   *
   * @return $this
   */
  public function provideContext($name) {
    $this->providedContext[] = $name;
    return $this;
  }

  /**
   * Returns the names of context that is provided back to the caller.
   *
   * @return string[]
   *   The names of the context that is provided back.
   */
  public function getProvidedContext() {
    return $this->providedContext;
  }

  /**
   * Sets the value of a context.
   *
   * @param string $name
   *   The name.
   * @param mixed $value
   *   The context value.
   *
   * @return $this
   *
   * @throws \Drupal\rules\Exception\LogicException
   *   Thrown if the passed context is not defined.
   */
  public function setContextValue($name, $value) {
    if (!isset($this->contextDefinitions[$name])) {
      throw new LogicException("The specified context '$name' is not defined.");
    }
    $this->state->setVariable($name, $this->contextDefinitions[$name], $value);
    return $this;
  }

  /**
   * Executes the component with the previously set context.
   *
   * @return mixed[]
   *   The array of provided context values, keyed by context name.
   *
   * @throws \Drupal\rules\Exception\EvaluationException
   *   Thrown if the Rules expression triggers errors during execution.
   */
  public function execute() {
    $this->expression->executeWithState($this->state);
    $this->state->autoSave();
    $result = [];
    foreach ($this->providedContext as $name) {
      $result[$name] = $this->state->getVariableValue($name);
    }
    return $result;
  }

  /**
   * Executes the component with the given values.
   *
   * @param mixed[] $arguments
   *   The array of arguments; i.e., an array keyed by name of the defined
   *   context and the context value as argument.
   *
   * @return mixed[]
   *   The array of provided context values, keyed by context name.
   *
   * @throws \Drupal\rules\Exception\LogicException
   *   Thrown if the context is not defined.
   * @throws \Drupal\rules\Exception\EvaluationException
   *   Thrown if the Rules expression triggers errors during execution.
   */
  public function executeWithArguments(array $arguments) {
    $this->state = ExecutionState::create();
    foreach ($arguments as $name => $value) {
      $this->setContextValue($name, $value);
    }
    return $this->execute();
  }

  /**
   * Verifies that the given expression is valid with the defined context.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   A list object containing \Drupal\rules\Engine\IntegrityViolation objects.
   */
  public function checkIntegrity() {
    $metadata_state = $this->getMetadataState();
    return $this->expression->checkIntegrity($metadata_state);
  }

  /**
   * Gets the metadata state with all context definitions as variables in it.
   *
   * Describes the metadata state before execution - only context definitions
   * are set as variables.
   *
   * @return \Drupal\rules\Engine\ExecutionMetadataStateInterface
   *   The execution metadata state populated with context definitions.
   */
  public function getMetadataState() {
    $data_definitions = [];
    foreach ($this->contextDefinitions as $name => $context_definition) {
      $data_definitions[$name] = $context_definition->getDataDefinition();
    }

    return ExecutionMetadataState::create($data_definitions);
  }

  /**
   * Calculates dependencies for the component.
   *
   * @return array
   *   An array of dependencies grouped by type (config, content, module,
   *   theme).
   *
   * @see \Drupal\Component\Plugin\DependentPluginInterface::calculateDependencies()
   */
  public function calculateDependencies() {
    // @todo: Complete implementation and add test coverage.
    $this->addDependency('module', 'rules');
    $this->addDependencies($this->getExpression()->calculateDependencies());
    return $this->dependencies;
  }

  /**
   * PHP magic __clone function.
   */
  public function __clone() {
    // Implement a deep clone.
    $this->state = clone $this->state;
    $this->expression = clone $this->expression;
  }

  /**
   * Returns autocomplete results for the given partial selector.
   *
   * Example: "node.uid.e" will return ["node.uid.entity"].
   *
   * @param string $partial_selector
   *   The partial data selector.
   * @param \Drupal\rules\Engine\ExpressionInterface $until
   *   The expression in which the autocompletion will be executed. All
   *   variables in the execution metadata state up to that point are available.
   *
   * @return array[]
   *   A list of autocomplete suggestions - valid property paths for one of the
   *   provided data definitions. Each entry is an array with the following
   *   keys:
   *   - value: the data selecor property path.
   *   - label: the human readable label suggestion.
   */
  public function autocomplete($partial_selector, ExpressionInterface $until = NULL) {
    // We use the integrity check to populate the execution metadata state with
    // available variables.
    $metadata_state = $this->getMetadataState();
    $this->expression->prepareExecutionMetadataState($metadata_state, $until);

    return $metadata_state->autocomplete($partial_selector);
  }

}
