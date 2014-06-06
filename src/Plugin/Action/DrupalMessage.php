<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action/DrupalMessage.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Show a message on the site' action.
 *
 * @Action(
 *   id = "rules_drupal_message",
 *   label = @Translation("Show a message on the site")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class DrupalMessage extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['message'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Message'));
    // @todo This should be a restricted list.
    $contexts['type'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Message type'));

    // @todo setDefaultValue() is missing on the ContextDefinition class.
    $contexts['repeat'] = ContextDefinition::create($typed_data_manager, 'boolean')
      ->setLabel(t('Repeat message'))
      ->setDescription(t("If disabled and the message has been already shown, then the message won't be repeated."))
      ->setRequired(FALSE);

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Show a message on the site.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // @todo Should we do the sanitization somewhere else? D7 had the sanitize
    // flag in the context definition.
    $message = check_plain($this->getContextValue('message'));
    $type = $this->getContextValue('type');
    $repeat = $this->getContextValue('repeat');
    if (!$repeat) {
      $repeat = FALSE;
    }
    // @todo This should be an injectable service, so that we can write a proper
    // unit test.
    drupal_set_message($message, $type, $repeat);
  }

  public function executeMultiple(array $objects) {
    // @todo: Remove this once it is removed from the interface.
  }

}
