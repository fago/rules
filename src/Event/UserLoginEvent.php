<?php

/**
 * @file
 * Contains \Drupal\rules\Event\UserLoginEvent.
 */

namespace Drupal\rules\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event that is fired when a user logs in.
 *
 * @see rules_user_login()
 */
class UserLoginEvent extends GenericEvent {

  const EVENT_NAME = 'rules_user_login';

}
