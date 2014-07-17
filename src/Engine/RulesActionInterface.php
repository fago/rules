<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesActionInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Context\ProvidedContextPluginInterface;

/**
 * Extends the core ActionInterface to provide context.
 */
interface RulesActionInterface extends ActionInterface, ContextAwarePluginInterface, ProvidedContextPluginInterface {

  /**
   * Returns a list of context names that should be auto-saved after execution.
   *
   * @return array
   *   A subset of context names as specified in the context definition of this
   *   action.
   */
  public function autoSaveContext();

}
