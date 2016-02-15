<?php

/**
 * @file
 * Contains \Drupal\rules\Form\ReactionRuleEditForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Core\RulesEventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to edit a reaction rule.
 */
class ReactionRuleEditForm extends RulesComponentFormBase {

  use TempStoreTrait;

  /**
   * The event plugin manager.
   *
   * @var \Drupal\rules\Core\RulesEventManager
   */
  protected $eventManager;

  /**
   * Constructs a new object of this class.
   *
   * @param \Drupal\rules\Core\RulesEventManager $event_manager
   *   The event plugin manager.
   */
  public function __construct(RulesEventManager $event_manager) {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.rules_event'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $this->addLockInformation($form);

    foreach ($this->entity->getEventNames() as $key => $event_name) {
      $event_definition = $this->eventManager->getDefinition($event_name);
      $form['event'][$key] = [
        '#type' => 'item',
        '#title' => $this->t('Events') . ':',
        '#markup' => $this->t('@label (@name)', [
          '@label' => $event_definition['label'],
          '@name' => $event_name,
        ]),
      ];
    }
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
    $actions['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancel'],
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    // Also remove the temporarily stored rule, it has been persisted now.
    $this->deleteFromTempStore();

    drupal_set_message($this->t('Reaction rule %label has been updated.', ['%label' => $this->entity->label()]));
  }

  /**
   * Form submission handler for the 'cancel' action.
   */
  public function cancel(array $form, FormStateInterface $form_state) {
    $this->deleteFromTempStore();
    drupal_set_message($this->t('Canceled.'));
    $form_state->setRedirect('entity.rules_reaction_rule.collection');
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

}
