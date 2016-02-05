<?php

/**
 * @file
 * Contains \Drupal\rules\EventHandler\EventHandlerEntityBundle.
 */

namespace Drupal\rules\EventHandler;
use Symfony\Component\EventDispatcher\Event;

/**
 * Exposes the bundle of an entity as event setting.
 */
class EventHandlerEntityBundle extends EventHandlerBase {

  /**
   * @inheritdoc
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition) {
    $events_suffixes = [];
    if ($event instanceof \Drupal\rules\Event\EntityEvent) {
      $events[] = $event->getSubject()->bundle();
    }
    return $events_suffixes;
  }

}
