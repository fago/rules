<?php

/**
 * @file
 * Contains \Drupal\rules\Form\ReactionRuleAddForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Core\RulesConfigurableEventHandlerInterface;
use Drupal\rules\Core\RulesEventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to add a reaction rule.
 */
class ReactionRuleAddForm extends RulesComponentFormBase {

  /**
   * The Rules event manager.
   *
   * @var \Drupal\rules\Core\RulesEventManager
   */
  protected $eventManager;

  /**
   * Constructs a new reaction rule form.
   *
   * @param \Drupal\rules\Core\RulesEventManager $event_manager
   *   The Rules event manager.
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

    $event_definitions = $this->eventManager->getGroupedDefinitions();
    $options = [];
    foreach ($event_definitions as $group => $definitions) {
      foreach ($definitions as $id => $definition) {
        $options[$group][$id] = $definition['label'];
      }
    }

    $form['events']['#tree'] = TRUE;
    $form['events'][]['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('React on event'),
      '#options' => $options,
      '#required' => TRUE,
      '#ajax' => $this->getDefaultAjax(),
      '#description' => $this->t('Whenever the event occurs, rule evaluation is triggered.'),
      '#executes_submit_callback' => array('::submitForm'),
    ];

    $form['event_configuration'] = array();
    if ($values = $form_state->getValue('events')) {
      $event_name = $values[0]['event_name'];
      if ($handler = $this->getEventHandler($event_name)) {
        $form['event_configuration'] = $handler->buildConfigurationForm(array(), $form_state);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $entity = parent::buildEntity($form, $form_state);
    foreach ($entity->getEventBaseNames() as $event_name) {
      if ($handler = $this->getEventHandler($event_name)) {
        $handler->extractConfigurationFormValues($form['event_configuration'], $form_state);
        $entity->set('configuration', $handler->getConfiguration());
        $entity->set('events', [['event_name' => $event_name . '--' . $handler->getConfiguration()['bundle']]]);
      }
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    drupal_set_message($this->t('Reaction rule %label has been created.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.rules_reaction_rule.edit_form', ['rules_reaction_rule' => $this->entity->id()]);
  }

  /**
   * Gets event handler class.
   *
   * Currently event handler is available only when the event is configurable.
   *
   * @param $event_name
   *   The event base name.
   * @param array $configuration
   *   The event configuration.
   *
   * @return \Drupal\rules\Core\RulesConfigurableEventHandlerInterface|null
   *   The event handler, null if there is no proper handler.
   */
  protected function getEventHandler($event_name, $configuration = []) {
    $event_definition = $this->eventManager->getDefinition($event_name);
    $handler_class = $event_definition['class'];
    if (is_subclass_of($handler_class, RulesConfigurableEventHandlerInterface::class)) {
      $handler = new $handler_class($configuration, $this->eventManager->getEventBaseName($event_name), $event_definition);
      return $handler;
    }
  }

}
