<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Core\RulesEventManager;
use Drupal\rules\Engine\ExpressionManagerInterface;
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
   * @param \Drupal\rules\Engine\ExpressionManagerInterface $expression_manager
   *   The expression manager.
   * @param \Drupal\rules\Core\RulesEventManager $event_manager
   *   The Rules event manager.
   */
  public function __construct(ExpressionManagerInterface $expression_manager, RulesEventManager $event_manager) {
    parent::__construct($expression_manager);
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.rules_expression'), $container->get('plugin.manager.rules_event'));
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
    $form_state->setRedirect('entity.rules_reaction_rule.edit_form', ['rules_reaction_rule' => $this->entity->id()]);
  }

}
