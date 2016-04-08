<?php

namespace Drupal\rules\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event that is fired when a logger-item is created.
 */
class SystemLoggerEvent extends GenericEvent {

  const EVENT_NAME = 'rules_system_logger_event';

}
