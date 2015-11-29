<?php

/**
 * @file
 * Contains \Drupal\rules\EventSubscriber\RedirectEventSubscriber.
 */

namespace Drupal\rules\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Changes the response to a redirect, if a redirect rules action was executed .
 */
class RedirectEventSubscriber implements EventSubscriberInterface {

  /**
   * Checks is a redirect rules action was executed, and redirects to the
   * provided url.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function checkRedirectIssued(FilterResponseEvent $event) {
    $request = $event->getRequest();
    $redirect_url = $request->attributes->get('_rules_redirect_action_url');
    if (isset($redirect_url)) {
      $event->setResponse(new RedirectResponse($redirect_url));
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('checkRedirectIssued');
    return $events;
  }

}
