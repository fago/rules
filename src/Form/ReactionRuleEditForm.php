<?php

/**
 * @file
 * Contains \Drupal\rules\Form\ReactionRuleEditForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\RulesEventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a form to edit a reaction rule.
 */
class ReactionRuleEditForm extends RulesComponentFormBase {

  use TempStoreTrait;

  /**
   * The event plugin manager.
   *
   * @var \Drupal\rules\Engine\RulesEventManager
   */
  protected $eventManager;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface.
   */
  protected $eventDispatcher;

  /**
   * The generic event subscriber.
   *
   * @var \Symfony\Component\EventDispatcher\EventSubscriberInterface
   */
  protected $genericEventSubscriber;

  /**
   * The Drupal kernel.
   *
   * @var \Drupal\Core\DrupalKernelInterface.
   */
  protected $drupalKernel;

  /**
   * Constructs a new object of this class.
   *
   * @param \Drupal\rules\Engine\RulesEventManager $event_manager
   *   The event plugin manager.
   */
  public function __construct(RulesEventManager $event_manager, EventDispatcherInterface $event_dispatcher, EventSubscriberInterface $generic_event_subscriber, DrupalKernelInterface $drupal_kernel) {
    $this->eventManager = $event_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->genericEventSubscriber = $generic_event_subscriber;
    $this->drupalKernel = $drupal_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.rules_event'),
      $container->get('event_dispatcher'),
      $container->get('rules.event_subscriber'),
      $container->get('kernel')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $this->addLockInformation($form);

    $event_name = $this->entity->getEvent();
    $event_definition = $this->eventManager->getDefinition($event_name);
    $form['event']['#markup'] = $this->t('Event: @label (@name)', [
      '@label' => $event_definition['label'],
      '@name' => $event_name,
    ]);
    $form_handler = $this->entity->getExpression()->getFormHandler();
    $form = $form_handler->form($form, $form_state);
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    // Also remove the temporarily stored rule, it has been persisted now.
    $this->deleteFromTempStore();

    // After the reaction rule is saved, we need to rebuild the container,
    // otherwise the reaction rule will not fire. However, we can do an
    // optimization: if our generic event subscriber is already registered to
    // the event in the kernel/container then we don't need to rebuild.
    if (!$this->isRuleEventRegistered()) {
      $this->drupalKernel->rebuildContainer();
    }

    drupal_set_message($this->t('Reaction rule %label has been updated.', ['%label' => $this->entity->label()]));
  }

  /**
   * Title callback: also display the rule label.
   */
  public function getTitle($rules_reaction_rule) {
    return $this->t('Edit reaction rule "@label"', ['@label' => $rules_reaction_rule->label()]);
  }

  /**
   * Returns the entity object, which is the rules config on this class.
   *
   * @see \Drupal\rules\Form\TempStoreTrait
   */
  protected function getRuleConfig() {
    return $this->entity;
  }

  /**
   * Checks if the event of the current rule is registered into the container.
   *
   * @return bool
   *   TRUE if the event is registered, FALSE otherwise.
   */
  protected function isRuleEventRegistered() {
    // To check if the event of the rule is registered, we have to check if the
    // generic subscriber is registered for the event. In order to check if the
    // generic subscriber is already registered for the event, we have to search
    // in the listeners list for an object with the same class as our generic
    // event subscriber which is registered for that event.
    $event_name = $this->getRuleConfig()->getEvent();
    $listeners = $this->eventDispatcher->getListeners();
    if (!empty($listeners[$event_name])) {
      $generic_subscriber_class = get_class($this->genericEventSubscriber);
      foreach ($listeners[$event_name] as $listener) {
        if (is_object($listener[0]) && get_class($listener[0]) == $generic_subscriber_class) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}
