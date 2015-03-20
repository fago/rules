<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesActionInterface.
 */

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Context\ContextProviderInterface;

/**
 * Extends the core ActionInterface to provide context.
 */
interface RulesActionInterface extends ActionInterface, ContextAwarePluginInterface, ContextProviderInterface {

  /**
   * Returns a list of context names that should be auto-saved after execution.
   *
   * @return array
   *   A subset of context names as specified in the context definition of this
   *   action.
   */
  public function autoSaveContext();

}
