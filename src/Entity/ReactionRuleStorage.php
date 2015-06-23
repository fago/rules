<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\ReactionRuleStorage.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Storage handler for reaction rule config entities.
 */
class ReactionRuleStorage extends ConfigEntityStorage {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateService;

  /**
   * Constructs a ReactionRuleStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\State\StateInterface $state_service
   *   The state service.
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, StateInterface $state_service) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager);

    $this->stateService = $state_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('state')
    );
  }

  /**
   * Returns a list of event names that are used by active reaction rules.
   *
   * @return string[]
   *   The list of event names keyed by event name.
   */
  protected function getRegisteredEvents() {
    $events = [];
    foreach ($this->loadMultiple() as $rules_config) {
      $event = $rules_config->getEvent();
      if ($event && !isset($events[$event])) {
        $events[$event] = $event;
      }
    }
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    $return = parent::save($entity);

    // Update the state of registered events.
    // @todo Should we trigger a container rebuild here as well? Might be a bit
    // expensive on every save?
    $this->stateService->set('rules.registered_events', $this->getRegisteredEvents());

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    $return = parent::delete($entities);

    // Update the state of registered events.
    // @todo Should we trigger a container rebuild here as well? Might be a bit
    // expensive on every delete?
    $this->stateService->set('rules.registered_events', $this->getRegisteredEvents());

    return $return;
  }

}
