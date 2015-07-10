<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\ReactionRuleAddForm.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\RulesEventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to add a reaction rule.
 */
class ReactionRuleAddForm extends RulesComponentFormBase {

  /**
   * The Rules event manager.
   *
   * @var \Drupal\rules\Engine\RulesEventManager
   */
  protected $eventManager;

  /**
   * Constructs a new reaction rule form.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The rules_component storage.
   */
  public function __construct(RulesEventManager $event_manager) {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.rules_event')
    );
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
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $event_defintions = $this->eventManager->getGroupedDefinitions();
    $options = [];
    foreach ($event_defintions as $group => $definitions) {
      foreach ($definitions as $id => $definition) {
        $options[$group][$id] = $definition['label'];
      }
    }

    $form['event'] = [
      '#type' => 'select',
      '#title' => $this->t('React on event'),
      '#options' => $options,
      '#required' => TRUE,
      '#empty_value' => $this->t('- Select -'),
      '#description' => $this->t('Whenever the event occurs, rule evaluation is triggered.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    drupal_set_message($this->t('Reaction rule %label has been created.', ['%label' => $this->entity->label()]));
  }

}
