<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ProvidedContextPluginInterface
 */

namespace Drupal\rules\Context;

/**
 * Extends the core ConditionInterface with negation and provided context.
 */
interface ProvidedContextPluginInterface {

  /**
   * Sets the value for a defined provided variable.
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
   * @return \Drupal\Component\Plugin\Context\ContextInterface
   *   The context object.
   */
  public function getProvided($name);

  /**
   * Gets a specific provided context definition of the plugin.
   *
   * @param string $name
   *   The name of the provided context in the plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the requested provided context is not defined.
   *
   * @return \Drupal\Component\Plugin\Context\ContextDefinitionInterface.
   *   The definition of the provided context.
   */
  public function getProvidedDefinition($name);

  /**
   * Gets the provided context definitions of the plugin.
   *
   * @return array
   *   The array of provided context definitions, keyed by context name.
   */
  public function getProvidedDefinitions();

}
