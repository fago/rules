<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Show a message on the site' action.
 *
 * @RulesAction(
 *   id = "rules_system_message",
 *   label = @Translation("Show a message on the site"),
 *   category = @Translation("System"),
 *   context = {
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Message type")
 *     ),
 *     "repeat" = @ContextDefinition("boolean",
 *       label = @Translation("Repeat message"),
 *       description = @Translation("If disabled and the message has been already shown, then the message won't be repeated."),
 *       default_value = NULL,
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class SystemMessage extends RulesActionBase {

  /**
   * Set a system message.
   *
   * @param string $message
   *   Message string that should be set.
   * @param string $type
   *   Type of the message.
   * @param bool $repeat
   *   (optional) TRUE if the message should be repeated.
   */
  protected function doExecute($message, $type, $repeat) {
    // @todo Should we do the sanitization somewhere else? D7 had the sanitize
    // flag in the context definition.
    $message = SafeMarkup::checkPlain($message);
    $repeat = (bool) $repeat;
    drupal_set_message($message, $type, $repeat);
  }

}
