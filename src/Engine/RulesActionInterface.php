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

}
