<?php

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for Rules event handlers.
 *
 * Event handlers provide access to the metadata of events.
 *
 * @see \Drupal\rules\Core\RulesDefaultEventHandler
 */
interface RulesEventHandlerInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Gets the context definitions of the event.
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
   * @return \Drupal\rules\Context\ContextDefinitionInterface
   *   The definition against which the context value must validate.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the requested context is not defined.
   */
  public function getContextDefinition($name);

}
