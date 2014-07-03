<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\DrupalMessage.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Component\Utility\String;
use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Show a message on the site' action.
 *
 * @Action(
 *   id = "rules_drupal_message",
 *   label = @Translation("Show a message on the site"),
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
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class DrupalMessage extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Show a message on the site');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // @todo Should we do the sanitization somewhere else? D7 had the sanitize
    // flag in the context definition.
    $message = String::checkPlain($this->getContextValue('message'));
    $type = $this->getContextValue('type');
    $repeat = (bool) $this->getContextValue('repeat');
    drupal_set_message($message, $type, $repeat);
  }

}
