<?php

namespace Drupal\rules\Context;

/**
 * Interface for plugins that provide context.
 *
 * This interface allows plugins to provide new context; e.g., an action plugin
 * that loads a user would provide the user entity.
 *
 * The plugin has to specify an array of context definitions for the provided
 * context under the "provides" key at the plugin definition, keyed by provided
 * context name.
 */
interface ContextProviderInterface {

  /**
   * Sets the value for a provided context.
   *
   * @param string $name
   *   The name of the provided context in the plugin definition.
   * @param mixed $value
   *   The value to set the provided context to.
   *
   * @return $this
   */
  public function setProvidedValue($name, $value);

  /**
   * Gets a defined provided context.
   *
   * @param string $name
   *   The name of the provided context in the plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the requested provided context is not set.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface
   *   The context object.
   */
  public function getProvidedContext($name);

  /**
   * Gets a specific provided context definition of the plugin.
   *
   * @param string $name
   *   The name of the provided context in the plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the requested provided context is not defined.
   *
   * @return \Drupal\Component\Plugin\Context\ContextDefinitionInterface
   *   The definition of the provided context.
   */
  public function getProvidedContextDefinition($name);

  /**
   * Gets the provided context definitions of the plugin.
   *
   * @return \Drupal\Component\Plugin\Context\ContextDefinitionInterface[]
   *   The array of provided context definitions, keyed by context name.
   */
  public function getProvidedContextDefinitions();

}
