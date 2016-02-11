<?php

/**
 * @file
 * Contains \Drupal\rules\EventHandler\ConfigurableEventHandlerEntityBundle.
 */

namespace Drupal\rules\EventHandler;

use Symfony\Component\EventDispatcher\Event;

/**
 * Exposes the bundle of an entity as event setting.
 */
class ConfigurableEventHandlerEntityBundle extends ConfigurableEventHandlerBase {

  /**
   * {@inheritdoc}
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition) {
    $events_suffixes = [];
    if ($event instanceof \Drupal\rules\Event\EntityEvent) {
      $events_suffixes[] = $event->getSubject()->bundle();
    }
    return $events_suffixes;
  }

}
