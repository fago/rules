<?php

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
    $this->conditions = $this->rule->conditionContainer();
  }

  public function form(&$form, &$form_state, $options = array()) {
    $form_state['rules_element'] = $this->rule;
    $label = $this->element->label();
    // Automatically add a counter to unlabelled rules.
    if ($label == t('unlabeled') && !$this->element->isRoot() && !empty($options['init'])) {
      $parent = $this->element->parentElement();
      $label .= ' ' . count($parent->getIterator());
    }
    // Components have already a label. If used inside a rule-set add a label
    // though. It's called 'Name' in the UI though.
    if (!$this->element->isRoot()) {
      $form['label'] = array(
        '#type' => 'textfield',
        '#title' => t('Name'),
        '#default_value' => empty($options['init']) ? $label : '',
        '#required' => TRUE,
        '#weight' => 5,
        '#description' => t('A human-readable name shortly describing the rule.'),
      );
    }

    $form += array('conditions' => array('#weight' => -5, '#tree' => TRUE));
    $this->conditions->form($form['conditions'], $form_state);
    unset($form['conditions']['negate']);

    // Add actions form.
    $iterator = new RecursiveIteratorIterator($this->rule->actions(), RecursiveIteratorIterator::SELF_FIRST);
    parent::form($form, $form_state, $options, $iterator);
    // Hide nested elements during creation.
    $form['elements']['#access'] = empty($options['init']);
    $form['conditions']['elements']['#access'] = empty($options['init']);

    $form_state['redirect'] = RulesPluginUI::path($this->element->root()->name, 'edit', $this->element);
    if (!empty($options['button'])) {
      $form['submit']['#value'] = t('Save changes');
    }
  }

  /**
   * Applies the values of the form to the rule configuration.
   */
  function form_extract_values($form, &$form_state) {
    // Run condition and action container value extraction.
    if (isset($form['conditions'])) {
      $this->conditions->extender('RulesConditionContainerUI')->form_extract_values($form['conditions'], $form_state);
    }
    if (!empty($form_state['values']['label'])) {
      $this->element->label = $form_state['values']['label'];
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
      '#access' => empty($options['init']),
    );

    $event_info = rules_fetch_data('event_info');
    $form['events']['table'] = array(
      '#theme' => 'table',
      '#caption' => 'Events',
      '#header' => array('Event', 'Operations'),
      '#empty' => t('None'),
    );
    $form['events']['table']['#attributes']['class'][] = 'rules-elements-table';
    foreach ($this->rule->events() as $event_name) {
      $form['events']['table']['#rows'][$event_name] = array(
        check_plain($event_info[$event_name]['label']),
        '<span class="rules_rule_event">' . l(t('delete'), RulesPluginUI::path($this->rule->name, 'delete/event/' . $event_name)) . '</span>',
      );
    }

    // Add the "add event" row.
    $cell['colspan'] = 3;
    $cell['data']['#theme'] = 'links__rules';
    $cell['data']['#attributes']['class'][] = 'rules-operations-add';
    $cell['data']['#attributes']['class'][] = 'action-links';
    $cell['data']['#links']['add_event'] = array(
      'title' => t('Add event'),
      'href' => RulesPluginUI::path($this->rule->name, 'add/event'),
      'query' => drupal_get_destination(),
    );
    $form['events']['table']['#rows'][] = array('data' => array($cell), 'class' => array('rules-elements-add'));

    parent::form($form, $form_state, $options);
    unset($form['label']);
  }

  /**
   * Adds the configuration settings form (label, tags, description, ..).
   */
  public function settingsForm(&$form, &$form_state) {
    parent::settingsForm($form, $form_state);
    $form['settings']['active'] = array(
      '#type' => 'checkbox',
      '#title' => t('Active'),
      '#default_value' => !isset($this->rule->active) || $this->rule->active,
    );
    $form['settings']['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#default_value' => $this->element->weight,
      '#weight' => 5,
      '#delta' => 10,
      '#description' => t('Order rules that react on the same event. Rules with a higher weight are evaluated after rules with less weight.'),
    );
    unset($form['settings']['component_provides']);
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
    $form['elements']['#add']['#links'] = array_intersect_key($form['elements']['#add']['#links'], array('add_rule' => 1));
    $form['elements']['#attributes']['class'][] = 'rules-rule-set';
    $form['elements']['#caption'] = t('Rules');
  }
}

/**
 * UI for Rules loops.
 *
 * @todo Make the current list item variable name and label configurable.
 */
class RulesLoopUI extends RulesActionContainerUI {

  function form_extract_values($form, &$form_state) {
    parent::form_extract_values($form, $form_state);

    // Make sure the current list item settings are set.
    $this->element->settings += array(
      'item:var' => 'list_item',
      'item:label' => t('Current list item'),
    );
  }
}
