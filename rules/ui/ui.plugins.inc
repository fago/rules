<?php
// $Id$

/**
 * @file Contains UI for diverse plugins provided by Rules.
 */

/**
 * Rule specific UI.
 */
class RulesRuleUI extends RulesActionContainerUI {

  protected $rule, $conditions;

  public function __construct(FacesExtendable $object) {
    parent::__construct($object);
    $this->rule = $object;
    // Make sure the condition is intialized and then get it.
    $this->rule->conditions();
    $this->conditions = $this->property('conditions');
  }

  public function form(&$form, &$form_state, $options = array()) {
    $form += array('conditions' => array('#weight' => -5, '#tree' => TRUE));
    $this->conditions->form($form['conditions'], $form_state);
    unset($form['conditions']['negate']);
    $form['conditions']['add']['#access'] = empty($options['init']);

    // Add actions form.
    $iterator = new RecursiveIteratorIterator($this->rule->actions(), RecursiveIteratorIterator::SELF_FIRST);
    parent::form($form, $form_state, $options, $iterator);
    // For rules always show nested elements.
    $form['elements']['#access'] = empty($options['init']);
    $form['conditions']['elements']['#access'] = empty($options['init']);
  }

  /**
   * Applies the values of the form to the rule configuration.
   */
  function form_extract_values($form, &$form_state) {
    // Run condition and action container value extraction.
    if (isset($form['conditions'])) {
      $this->conditions->extender('RulesConditionContainerUI')->form_extract_values($form['conditions'], $form_state);
    }
    parent::form_extract_values($form, $form_state);
  }


  public function operations() {
    // When rules are listed only show the edit and delete operations.
    $ops = parent::operations();
    $ops['#links'] = array_intersect_key($ops['#links'], array_flip(array('edit', 'delete')));
    return $ops;
  }
}

/**
 * Reaction rule specific UI.
 */
class RulesReactionRuleUI extends RulesRuleUI {

  public function form(&$form, &$form_state, $options = array()) {
    $form['events'] = array(
      '#type' => 'container',
      '#weight' => -10,
    );

    $event_info = rules_fetch_data('event_info');
    $form['events']['table'] = array(
      '#theme' => 'table',
      '#header' => array('Event', 'Operations'),
      '#empty' => t('None'),
    );
    foreach ($this->rule->events() as $event_name) {
      $form['events']['table']['#rows'][$event_name] = array(
        check_plain($event_info[$event_name]['label']),
        '<span class="rules_rule_event">' . l(t('delete'), RulesPluginUI::path($this->rule->name, 'event/delete/' . $event_name)) . '</span>',
      );
    }

    $form['events']['add']['#theme'] = 'links__rules';
    $form['events']['add']['#attributes']['class'][] = 'rules-operations-add';
    $form['events']['add']['#attributes']['class'][] = 'action-links';
    $form['events']['add']['#links']['add_event'] = array(
      'title' => t('Add event'),
      'href' => RulesPluginUI::path($this->rule->name, 'add/event'),
      'query' => drupal_get_destination(),
    );

    parent::form($form, $form_state, $options);
  }

  /**
   * Adds the configuration settings form (label, tags, description, ..).
   */
  public function settingsForm(&$form, &$form_state) {
    parent::settingsForm($form, $form_state);
    $form['settings']['active'] = array(
      '#type' => 'checkbox',
      '#title' => t('Active'),
      '#description' => t('If checked the reaction rule will be triggered when the configured events occur.'),
      '#default_value' => !isset($this->rule->active) || $this->rule->active,
    );
    $form['settings']['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#default_value' => $this->element->weight,
      '#weight' => 5,
      '#delta' => 10,
      '#description' => t('Adjust the weight to influence the evaluation order of rules reacting on the same event.'),
    );
  }

  public function settingsFormExtractValues($form, &$form_state) {
    parent::settingsFormExtractValues($form, $form_state);
    $this->rule->active = $form_state['values']['active'];
    $this->rule->weight = $form_state['values']['weight'];
  }
}

/**
 * Rule set specific UI.
 */
class RulesRuleSetUI extends RulesActionContainerUI {

  public function form(&$form, &$form_state, $options = array(), $iterator = NULL) {
    // Pass an iterator just iterating over the rules, thus no further child
    // elements will be displayed.
    parent::form($form, $form_state, $options, $this->element->getIterator());
    // Only show the add rule link.
    $form['add']['#links'] = array_intersect_key($form['add']['#links'], array('add_rule' => 1));
    $form['elements']['#attributes']['class'][] = 'rules-rule-set';
    $form['elements']['#heading'] = t('Rules');
  }
}


