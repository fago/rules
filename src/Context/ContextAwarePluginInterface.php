<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextAwarePluginInterface.
 */

namespace Drupal\rules\Context;

use \Drupal\Component\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;

/**
 * Interface for Rules-context aware plugins.
 */
interface ContextAwarePluginInterface extends CoreContextAwarePluginInterface {

  /**
   * Gets the context definitions of the plugin.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of context definitions, keyed by context name.
   */
  public function getContextDefinitions();

  /**
   * Gets a specific context definition of the plugin.
   *
   * @param string $name
   *   The name of the context in the plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   *   If the requested context is not defined.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface
   *   The definition against which the context value must validate.
   */
  public function getContextDefinition($name);

  /**
   * Gets the defined contexts.
   *
   * @return \Drupal\rules\Context\ContextInterface[]
   *   The set context objects.
   */
  public function getContexts();

  /**
   * Gets a defined context.
   *
   * @param string $name
   *   The name of the context in the plugin configuration.
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   *   If the requested context is not defined.
   *
   * @return \Drupal\rules\Context\ContextInterface
   *   The context object.
   */
  public function getContext($name);

  /**
   * Validates the set values for the defined contexts.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface
   *   A list of constraint violations. If the list is empty, validation
   *   succeeded.
   */
  public function validateContexts();

}