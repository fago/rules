<?php

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;
use Drupal\rules\Exception\LogicException;

/**
 * Class for value objects helping with context configuration.
 *
 * @see \Drupal\rules\Context\ContextHandlerTrait
 */
class ContextConfig {

  /**
   * The config array.
   *
   * @var array
   */
  protected $config = [
    'context_values' => [],
    'context_mapping' => [],
    'context_processors' => [],
    'provides_mapping' => [],
  ];

  /**
   * Creates a context config object.
   *
   * @param array $values
   *   (optional) Some initial values to set. In the same format as returned
   *   from static::toArray().
   *
   * @return $this
   */
  public static function create(array $values = []) {
    return new static($values);
  }

  /**
   * Constructs the object.
   *
   * @param array $values
   *   Some initial values to set. In the same format as returned from
   *   static::toArray().
   */
  protected function __construct(array $values) {
    $this->config = $values + $this->config;
  }

  /**
   * Maps the data specified by the selector to the given context.
   *
   * @param string $context_name
   *   The name of the context.
   * @param string $property_path
   *   A valid property path; e.g., "node.uid.target_id".
   *
   * @throws \Drupal\rules\Exception\LogicException
   *   Thrown if a context value and map are set for a given context at the same
   *   time.
   *
   * @return $this
   */
  public function map($context_name, $property_path) {
    if (isset($this->config['context_values'][$context_name])) {
      throw new LogicException("Cannot map a context value and pre-define it at the same time.");
    }
    $this->config['context_mapping'][$context_name] = $property_path;
    return $this;
  }

  /**
   * Sets a pre-defined value for the given context.
   *
   * @param string $context_name
   *   The name of the context.
   * @param mixed $value
   *   The value to set for the context. The value must he a valid value for the
   *   context's data type, unless a data processor takes care of processing it
   *   to a valid value.
   *
   * @throws \Drupal\rules\Exception\LogicException
   *   Thrown if a context value and map are set for a given context at the same
   *   time.
   *
   * @return $this
   */
  public function setValue($context_name, $value) {
    if (isset($this->config['context_mapping'][$context_name])) {
      throw new LogicException("Cannot map a context value and pre-define it at the same time.");
    }
    $this->config['context_values'][$context_name] = $value;
    return $this;
  }

  /**
   * Maps the name of a provided context.
   *
   * @param string $provided_context_name
   *   The name of the provided context.
   * @param string $context_name
   *   The context name under which the provided context should be registered.
   *
   * @return $this
   */
  public function provideAs($provided_context_name, $context_name) {
    $this->config['provides_mapping'][$provided_context_name] = $context_name;
    return $this;
  }

  /**
   * Sets an arbitrary configuration value under the given key.
   *
   * This may be used for setting any configuration options that are not making
   * use of the context system.
   *
   * @param string $key
   *   The config key to set.
   * @param mixed $value
   *   The value to set for the config key.
   *
   * @return $this
   */
  public function setConfigKey($key, $value) {
    $this->config[$key] = $value;
    return $this;
  }

  /**
   * Configures a data processor for the given context.
   *
   * @param string $context_name
   *   The name of the context.
   * @param string $plugin_id
   *   The id of the data processor plugin to use.
   * @param array $options
   *   (optional) An array of plugin configuration, as used by the plugin.
   *
   * @return $this
   */
  public function process($context_name, $plugin_id, array $options = []) {
    $this->config['context_processors'][$context_name][$plugin_id] = $options;
    return $this;
  }

  /**
   * Negates the result of the plugin (or not).
   *
   * Only applicable to condition plugins.
   *
   * @param bool $bool
   *   Whether to negate the result.
   *
   * @return $this
   */
  public function negateResult($bool = TRUE) {
    return $this->setConfigKey('negate', $bool);
  }

  /**
   * Exports the configuration to an array.
   *
   * @return array
   *   The config array, with the following keys set:
   *   - context_map: An array of data selectors, keyed by context name.
   *   - context An array of context values, keyed by context.
   *   - context_processors: An array of data processor config, keyed by context
   *     name and process plugin id.
   *   - provides_mapping: An array of context names to use for provided
   *     context, keyed by provided context name.
   *   - Any other other config keys that have been set.
   */
  public function toArray() {
    return $this->config;
  }

  /**
   * Checks the config for the given plugin.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   An instance of the plugin for which the config has been created.
   *
   * @todo: Implement.
   */
  public function checkConfig(CoreContextAwarePluginInterface $plugin) {
    // @todo.
  }

}
