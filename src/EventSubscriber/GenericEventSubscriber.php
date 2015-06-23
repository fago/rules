<?php

/**
 * @file
 * Contains \Drupal\rules\EventSubscriber\GenericEventSubscriber.
 */

namespace Drupal\rules\EventSubscriber;

use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subsscribes to Symfony events and maps them to Rules events.
 */
class GenericEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity manager used for loading reaction rule config entities.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Register this listener for every event that is used by a reaction rule.
    $events = [];
    $callback = ['onRulesEvent', 100];

    // If there is no state service there is nothing we can do here.
    if (!\Drupal::hasService('state')) {
      return [];
    }

    // Since we cannot access the reaction rule config storage here we have to
    // use the state system to provide registered Rules events. The Reaction
    // Rule storage is responsible for keeping the registered events up to date
    // in the state system.
    // @see \Drupal\rules\Entity\ReactionRuleStorage
    $state = \Drupal::state();
    $registered_event_names = $state->get('rules.registered_events');
    if (!empty($registered_event_names)) {
      foreach ($registered_event_names as $event_name) {
        $events[$event_name][] = $callback;
      }
    }
    return $events;
  }

  /**
   * Reacts on the given event and invokes configured reaction rules.
   *
   * @param \Symfony\Component\EventDispatcher\GenericEvent $event
   *   The event object containing context for the event.
   * @param string $event_name
   *   The event name.
   */
  public function onRulesEvent(GenericEvent $event, $event_name) {
    // Load reaction rule config entities by $event_name.
    $storage = $this->entityManager->getStorage('rules_reaction_rule');
    // @todo Only load active reaction rules here.
    $configs = $storage->loadByProperties(['event' => $event_name]);

    // Loop over all rules and execute them.
    foreach ($configs as $rules_config) {
      $reaction_rule = $rules_config->getExpression();

      // Set the rest of arguments as further context values on the rule.
      foreach ($event->getArguments() as $name => $value) {
        $reaction_rule->setContextValue($name, $value);
      }

      $reaction_rule->execute();
    }
  }

}
