<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextAwarePluginBase.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\TypedData\TypedDataManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Base class for plugins that are context aware.
 */
abstract class ContextAwarePluginBase extends PluginBase implements ContextAwarePluginInterface {

  /**
   * The contexts of this plugin.
   *
   * @var \Drupal\rules\Context\ContextInterface[]
   */
  protected $contexts;

  /**
   * The context definitions of this plugin.
   *
   * @var \Drupal\rules\Context\ContextDefinitionInterface[]
   */
  protected $contextDefinitions;

  /**
   * The typed data manager used for creating the data types of the context.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * Constructs a new condition plugin instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TypedDataManager $typed_data_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->typedDataManager = $typed_data_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('typed_data_manager')
    );
  }

  /**
   * Gets the typed data manager.
   *
   * @return \Drupal\Core\TypedData\TypedDataManager
   *   The typed data manager.
   */
  public function getTypedDataManager() {
    return $this->typedDataManager;
  }

  /**
   * Sets the typed data manager.
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typedDataManager
   *   The typed data manager.
   *
   * @return $this
   */
  public function setTypedDataManager(TypedDataManager $typedDataManager) {
    $this->typedDataManager = $typedDataManager;
    return $this;
  }

  /**
   * Defines the needed context of this plugin.
   *
   * @todo: Can we make this abstract somehow?
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of context definitions, keyed by context name.
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinitions() {
    if (!isset($this->contextDefinitions)) {
      $this->contextDefinitions = static::contextDefinitions($this->typedDataManager);
    }
    return $this->contextDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinition($name) {
    $definitions = $this->getContextDefinitions();
    if (empty($definitions[$name])) {
      throw new ContextException("The $name context is not a valid context.");
    }
    return $definitions[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    // Make sure all context objects are initialized.
    foreach ($this->getContextDefinitions() as $name => $definition) {
      $this->getContext($name);
    }
    return $this->contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($name) {
    // Check for a valid context value.
    if (!isset($this->contexts[$name])) {
      $this->contexts[$name] = new Context($this->getContextDefinition($name), $this->typedDataManager);
    }
    return $this->contexts[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function setContext($name, ContextInterface $context) {
    $this->contexts[$name] = $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextValues() {
    $values = [];
    foreach ($this->getContextDefinitions() as $name => $definition) {
      $values[$name] = isset($this->contexts[$name]) ? $this->contexts[$name]->getContextValue() : NULL;
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextValue($name) {
    return $this->getContext($name)->getContextValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setContextValue($name, $value) {
    $this->getContext($name)->setContextValue($value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function validateContexts() {
    $violations = new ConstraintViolationList();
    // @todo: Implement symfony validator API to let the validator traverse
    // and set property paths accordingly.

    foreach ($this->getContexts() as $name => $context) {
      $violations->addAll($context->validate());
    }
    return $violations;
  }

}
