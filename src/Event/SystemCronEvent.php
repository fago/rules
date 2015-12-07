<?php

/**
 * @file
 * Contains \Drupal\rules\Event\SystemCronEvent.
 */

namespace Drupal\rules\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event that is fired when cron maintenance tasks are performed.
 *
 * @see rules_cron()
 */
class SystemCronEvent extends GenericEvent {

  const EVENT_NAME = 'rules_system_cron';

}
