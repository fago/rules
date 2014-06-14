<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesActionInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Action\ActionInterface;

/**
 * Extends the core ActionInterface to provide context.
 */
interface RulesActionInterface extends ActionInterface, ContextAwarePluginInterface {

}
