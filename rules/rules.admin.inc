<?php
// $Id$

/**
 * @file Rules Admin UI
 */
require_once drupal_get_path('module', 'rules') . '/rules.admin_rule_proxy.inc';

/**
 * Implementation of hook_help().
 */
function rules_help($path, $arg) {
  switch ($path) {
    case 'admin/rules/rule_sets':
      return '<p>' . t('Rule sets are similar in concept to subroutines and can be invoked by actions or manually by code or another module.') . '</p>';
    case 'admin/rules/trigger':
      return '<p>' . t('This is an overview about rules that are triggered by a certain event. A rule may contain conditions and actions, which are executed only when the conditions are met.') . '</p>';
  }
}


/**
 * Lists the available rules.
 */
function rules_admin_form_overview(&$form_state) {
  // Check if token module is present.
  rules_admin_check_token();
  $form['filter'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter'),
    '#collapsible' => TRUE,
    '#collapsed' => isset($form_state['values']),
  );
  $form['filter']['set'] = array(
    '#type' => 'select',
    '#title' => t('Filter by event'),
    '#options' => array(0 => '<All>') + rules_admin_get_grouped_labels(rules_get_events()),
    '#default_value' => isset($form_state['values']['set']) ? $form_state['values']['set'] : 0,
  );
  $form['filter']['category'] = array(
    '#type' => 'select',
    '#title' => t('Filter by category'),
    '#options' => array(0 => '<All>') + rules_admin_get_categories(),
    '#default_value' => isset($form_state['values']['category']) ? $form_state['values']['category'] : 0,
  );
  $form['filter']['submit'] = array('#type' => 'submit', '#value' => t('Filter'));

  $category = isset($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;
  $set = isset($form_state['values']['set']) ? 'event_'. $form_state['values']['set'] : FALSE;

  $form['active_header'] = array('#value' => '<h3>'. t('Active rules'). '</h3>');
  $form['active'] = rules_admin_overview_table(array('category' => $category, 'set' => $set, 'active' => TRUE));
  $form['active']['#suffix'] = '<br />';
  $form['inactive_header'] = array('#value' => '<h3>'. t('Inactive rules'). '</h3>');
  $form['inactive'] = rules_admin_overview_table(array('category' => $category, 'set' => $set, 'active' => FALSE));

  if (variable_get('rules_show_fixed', FALSE)) {
    $form['fixed_header'] = array('#value' => '<h3>'. t('Fixed rules'). '</h3>');
    $form['fixed'] = rules_admin_overview_table(array('category' => $category, 'set' => $set, 'active' => TRUE, 'fixed' => TRUE));
  }
  return $form;
}

function rules_admin_form_overview_submit($form_id, &$form_state) {
  $form_state['rebuild'] = TRUE;
}

/**
 * Returns a table of rules filtered by the given parameters
 *
 * Supported parameters: fixed, category, set, active.
 * Category and set may be set to NULL to hide their columns.
 */
function rules_admin_overview_table($params) {
  $rules = rules_get_configured_items('rules');
  _rules_element_defaults($rules);
  rules_sort_children($rules);

  //set parameter defaults
  $params += array('category' => NULL, 'set' => NULL, 'active' => TRUE, 'fixed' => FALSE);
  extract($params);

  $is_set = $set && strpos($set, 'event_') === FALSE;

  $header = array(t('Label'), $is_set ? t('Set') : t('Event'), t('Category'), t('Status'), t('Operations'));
  $rows = array();

  foreach (element_children($rules) as $name) {
    $rule = $rules[$name];
    _rules_element_defaults($rule);

    if ((!$category || in_array($category, $rule['#categories'])) && (!$set && strpos($rule['#set'], 'event_') === 0 || $rule['#set'] == $set) && $rule['#active'] == $active && ($rule['#status'] == 'fixed') == $fixed) {
      $path = RULES_ADMIN_RULE_PATH .'/'. $name;
      $ops = array();
      if ($rule['#status'] != 'fixed') {
        $ops[] = l(t('edit'), $path .'/edit');
      }
      if ($rule['#status'] == 'custom') {
        $ops[] = l(t('delete'), $path .'/delete', array('query' => drupal_get_destination()));
      }
      else if ($rule['#status'] == 'altered') {
        $ops[] = l(t('revert'), $path .'/revert', array('query' => drupal_get_destination()));
      }
      $ops[] = l(t('clone'), $path .'/clone', array(), drupal_get_destination());
      $categories = array_map('check_plain', $rule['#categories']);

      $rows[] = array(
      l($rule['#label'], $path .'/edit'),
      check_plain(rules_get_element_label(rules_get_rule_sets($rule['#set']))),
      implode(', ', $categories),
      rules_admin_configuration_status($rule['#status']),
      implode(' ', $ops),
      );
    }
  }

  if (count($rows)) {
    return array('#value' => theme('table', $header, $rows, array('class' => 'rules-rule-configurations')));
  }

  return array('#value' => '<p>'. t('None') .'</p>');
}

/**
 * Check to see if the token module is installed, and if not put up
 * a message.
 *
 * Only call this function if the user is already in a position for this to
 * be useful.
 */
function rules_admin_check_token() {
  if (!variable_get('rules_hide_token_message', FALSE) && !module_exists('token')) {
    drupal_set_message(t('If you install the token module from !href, token replacements will be supported. <a href="@hide">Hide this message.</a>', array('!href' => l('http://drupal.org/project/token', 'http://drupal.org/project/token'), '@hide' => url('admin/rules/settings'))));
  }
}

/**
 * Returns the status of the configuration as html.
 */
function rules_admin_configuration_status($status) {
  if ($status == 'altered') {
    $help = t('This rule has been provided by a module, but has been modified.');
    return '<span title="'. $help .'">'. t('Modified'). '</span>';
  }
  else if ($status == 'fixed') {
    $help = t('This rule has been provided by a module and can\'t be customized.');
    return '<span title="'. $help .'">'. t('Fixed') .'</span>';
  }
  else if ($status == 'custom') {
    $help = t('A custom defined rule.');
    return '<span title="'. $help .'">'. t('Custom'). '</span>';
  }
  $help = t('This rule has been provided by a module.');
  return '<span title="'. $help .'">'. t('Default') .'</span>';
}

/**
 * Returns the add rule form
 */
function rules_admin_form_add_rule(&$form_state, $set_info = FALSE) {

  $form = rules_admin_form_rule_settings(array(), (boolean)$set_info);
  $form['settings']['set']['#default_value'] = $set_info['name'];
  return $form;
}

function rules_admin_form_add_rule_submit($form_id, &$form_state) {
  $rule = isset($form_state['proxy']) ? $form_state['proxy']->get_rule() : array('#type' => 'rule');

  foreach (array('set', 'label', 'active', 'weight') as $key) {
    $rule['#'. $key] = $form_state['values'][$key];
  }
  $rule['#status'] = 'custom';

  //get an unique name
  $rule_name = _rules_admin_rule_get_new_unique_name();

  rules_item_save('rules', $rule_name, $rule);
  rules_clear_cache();
  drupal_set_message(t("The rule %label has been added.", array('%label' => $rule['#label'])));
  $form_state['redirect'] =  RULES_ADMIN_RULE_PATH .'/'. $rule_name;
}

/**
 * Returns the form for the settings of a rule
 *
 * @param $is_set
 *   When adding a new rule for a rule set, TRUE.
 */
function rules_admin_form_rule_settings($rule, $is_set = FALSE) {
  $form['settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Rule settings'),
    '#collapsible' => TRUE,
  );
  $form['settings']['label'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#description' => t('Choose an appropriate label for this rule.'),
    '#default_value' => isset($rule['#label']) ? $rule['#label'] : '',
    '#required' => TRUE,
  );
  $form['settings']['set'] = array(
    '#type' => 'select',
    '#default_value' => isset($rule['#set']) ? $rule['#set'] : '',
    '#required' => TRUE,
  );
  if ($is_set) {
    $form['settings']['set'] += array(
      '#title' =>t('Rule set'),
      '#options' => rules_admin_get_grouped_labels(rules_admin_get_compatible_sets($rule, rules_get_configured_items('rule_sets'))),
      '#description' => t('Select to which rule set this rule should belong.'),
    );
  }
  else {
    $form['settings']['set'] += array(
      '#title' => t('Event'),
      '#options' => rules_admin_get_grouped_labels(rules_admin_get_compatible_sets($rule, rules_get_event_sets())),
      '#description' => t('Select the event on which you want to evaluate this rule.'),
    );
  };
  $form['settings']['active'] = array(
    '#title' => t('This rule is active and should be evaluated when the associated event occurs.'),
    '#type' => 'checkbox',
    '#default_value' => isset($rule['#active']) ? $rule['#active'] : 1,
  );
  $form['settings']['weight'] = array(
    '#title' => t('Weight'),
    '#type' => 'weight',
    '#description' => t('Adjust the weight to customize the ordering of rules.'),
    '#default_value' => isset($rule['#weight']) ? $rule['#weight'] : 0,
  );
  $form['settings']['button'] = array('#type' => 'submit', '#weight' => 10, '#value' => t('Save changes'));
  return $form;
}

/**
 * Returns the form for editing a rule
 */
function rules_admin_form_edit_rule(&$form_state, $proxy) {

  $form_state['proxy'] = &$proxy;
  $rule = $proxy->get_rule();
  _rules_element_defaults($rule);

  $is_set = strpos($rule['#set'], 'event_') === FALSE;
  rules_admin_fix_breadcrumb($is_set, $rule['#set'], $proxy->get_set_info());
  $form = rules_admin_form_rule_settings($rule, $is_set);

  //help
  rules_admin_element_help($form, $proxy->get_set_info());

  $form['elements'] = array(
    '#type' => 'fieldset',
    '#title' => t('Rule elements'),
    '#collapsible' => TRUE,
  );

  //this will render the whole rule with the help of drupal_render(),
  //see rules.admin_render.inc
  $form['elements']['rule'] = $proxy->get_indexed_rule();
  $form['elements']['rule']['#name'] = $proxy->get_rule_name();
  $form['elements']['rule']['#theme'] = array('rules_admin_rule_render');

  return $form;
}

function rules_admin_form_edit_rule_submit($form, &$form_state) {
  $rule_ref = &$form_state['proxy']->get_rule();
  foreach (array('label', 'active', 'weight', 'set') as $key) {
    $rule_ref['#'. $key] = $form_state['values'][$key];
  }

  $form_state['proxy']->save_changes();
  drupal_set_message(t("The rule %label has been updated.", array('%label' => $rule_ref['#label'])));
}

/**
 * Returns the form for the add operation
 * This handles adding conditions and actions
 *
 * @param $type Either 'action' or 'condition' or 'op'
 * @param $parent_id The id of the the element where the condition / action is to be added
 */
function rules_admin_form_add(&$form_state, $proxy, $type, $parent_id = NULL) {
  $form_state['proxy'] = &$proxy;
  $rule = $proxy->get_rule();
  _rules_element_defaults($rule);

  if (in_array($type, array('action', 'condition', 'op'))) {
    if (!isset($form_state['element'])) {
      //initial step!
      $form_state += array('element' => array('#type' => $type), 'step' => 0);
    }
    $element =& $form_state['element'];
    $element += array('#id' => NULL);

    // Due to adding storage during the form build, the form will be rebuilt
    // immediately. So be sure to increase the step only out of a #submit callback.
    // Also see http://drupal.org/node/302240.

    if ($form_state['step'] == 0) {
      if (isset($parent_id) && is_array($parent = $proxy->get_element(intval($parent_id)))) {
        if (function_exists($function = 'rules_admin_form_add_'. $element['#type'])) {
          $form_state['parent_id'] = intval($parent_id);
          _rules_element_defaults($parent);
          $form = $function($form_state, $element, $parent);
          return rules_admin_form_pack_storage($form, $form_state);
        }
      }
    }
    else {
      if (function_exists($function = 'rules_admin_form_edit_'. $type)) {
        $form = $function($form_state, $element);
        return rules_admin_form_pack_storage($form, $form_state);
      }
    }
  }
  drupal_not_found();
  exit;
}

/**
 * Returns the form for the first action add page
 */
function rules_admin_form_add_action(&$form_state, &$element) {
  $vars = $form_state['proxy']->get_available_variables();
  $actions = rules_admin_filter_info($vars, rules_get_actions());
  $form['name'] = array(
    '#type' => 'select',
    '#title' => t('Select an action to add'),
    '#options' => rules_admin_get_grouped_labels($actions),
    '#required' => TRUE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#weight' => 10,
    '#value' => t('Forward'),
    '#submit' => array('rules_admin_form_add_submit'),
  );
  return $form;
}

/**
 * Returns the form for the first condition add page
 */
function rules_admin_form_add_condition(&$form_state, &$element, $parent) {

  if (!isset($parent['#type']) || isset($parent['#logical_op']) && $parent['#logical_op']) {
    $vars = $form_state['proxy']->get_available_variables(0);
    $conditions = rules_admin_filter_info($vars, rules_get_conditions());
    $form['name'] = array(
      '#type' => 'select',
      '#title' => t('Select the condition to add'),
      '#options' => rules_admin_get_grouped_labels($conditions),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#weight' => 10,
      '#value' => t('Forward'),
      '#submit' => array('rules_admin_form_add_submit'),
    );
    return $form;
  }
}

/**
 * Indenting a condition
 * Adds a logical operation and place the given condition element inside. We automatically
 * determine which operation is to be added.
 */
function rules_admin_form_add_op(&$form_state, $element, $parent) {
  $parent_ref =& $form_state['proxy']->get_element($form_state['parent_id']);

  //determine the appropriate operation and apply it
  $op = rules_admin_determine_operation($form_state['proxy'], $form_state['parent_id']);
  if (isset($parent_ref['#weight'])) {
    $weight = $parent_ref['#weight'];
    unset($parent_ref['#weight']);
  }
  $parent_ref = rules_configure($op, $parent_ref);

  if (isset($weight)) {
    //apply the weight of the element to the op
    $parent_ref['#weight'] = $weight;
  }

  //and save
  $form_state['proxy']->save_changes();
  drupal_goto(RULES_ADMIN_RULE_PATH .'/'. $form_state['proxy']->get_rule_name());
}

function rules_admin_form_add_submit($form, &$form_state) {
  $form_state['element']['#name'] = $form_state['values']['name'];
  $form_state['step']++;
  rules_init_element_info($form_state['element']);
}

/**
 * Use this function when there should be a further step.
 * It puts all variables needed into the form storage
 */
function rules_admin_form_pack_storage($form, &$form_state) {
  foreach (array('proxy', 'step', 'element', 'parent_id') as $key) {
    if (isset($form_state[$key])) {
      $form_state['storage'][$key] =& $form_state[$key];
    }
  }
  // Register an after build callback that unpacks the storage.
  $form['#after_build'][] = 'rules_admin_form_unpack_storage';
  return $form;
}

/**
 * Puts the variables out of storage on their usual place. Invoked through
 * #after_build, which is set by rules_admin_form_pack_storage().
 */
function rules_admin_form_unpack_storage($form, &$form_state) {
  if (!isset($form_state['unpacked'])) {
    foreach (array('proxy', 'step', 'element', 'parent_id') as $key) {
      if (isset($form_state['storage'][$key])) {
        $form_state[$key] =& $form_state['storage'][$key];
      }
    }
    $form_state['unpacked'] = TRUE;
  }
  return $form;
}


/**
 * Returns the form for the edit operation
 * This handles editing conditions and actions
 *
 * @param $id The id of the the element where the condition / action is to be added
 */
function rules_admin_form_edit(&$form_state, &$proxy, $element) {
  if (!isset($form_state['element'])) {
    //initial step!
    $form_state += array('element' => $element, 'step' => 1);
  }
  //just call the add form with the appropriate step.
  $type = in_array($element['#type'], array('action', 'condition')) ? $element['#type'] : 'op';
  return rules_admin_form_add($form_state, $proxy, $type);
}

/**
 * Returns the edit form for an action
 */
function rules_admin_form_edit_action(&$form_state, $element) {
  $label = rules_get_element_label($element);
  $form['label'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#description' => t('Customize the label for this action.'),
    '#default_value' => $label,
    '#required' => TRUE,
    '#weight' => -5,
  );
  if ($label) {
    drupal_set_title(t('Editing action %label', array('%label' => $label)));
  }
  $form['weight'] = array(
    '#title' => t('Weight'),
    '#type' => 'weight',
    '#description' => t('Adjust the weight to customize the ordering of actions.'),
    '#default_value' => isset($element['#weight']) ? $element['#weight'] : 0,
    '#weight' => 8,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#weight' => 10,
    '#value' => t('Save'),
    '#submit' => array('rules_admin_form_edit_action_submit', 'rules_admin_form_edit_save'),
  );
  if (isset($element['#id'])) {
    $form['delete'] = array(
      '#type' => 'submit',
      '#weight' => 11,
      '#value' => t('Delete'),
      '#submit' => array('rules_admin_form_edit_delete_submit'),
    );
  }
  rules_admin_element_help($form, $element);
  rules_admin_default_argument_form($form, $form_state, $element);
  rules_admin_new_variables_form($form, $form_state, $element);
  rules_admin_element_alter_form($form, $form_state, $element);
  return $form;
}

/*
 * Apply the changes to the element
 */
function rules_admin_form_edit_action_submit($form, &$form_state) {
  $element =& $form_state['element'];
  $element['#weight'] = $form_state['values']['weight'];

  rules_admin_default_argument_form_submit($form, $form_state, $element);
  rules_admin_new_variables_form_submit($form, $form_state, $element);
  rules_admin_save_element_label($form, $form_state, $element);
  rules_admin_element_alter_form_submit($form, $form_state, $element);
  drupal_set_message(t('The action %label has been saved.', array('%label' => rules_get_element_label($element))));
}


/**
 * Returns the edit form for a condition
 */
function rules_admin_form_edit_condition(&$form_state, $element) {
  $label = rules_get_element_label($element);
  $form['label'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#description' => t('Customize the label for this condition.'),
    '#default_value' => $label,
    '#required' => TRUE,
    '#weight' => -5,
  );
  if ($label) {
    drupal_set_title(t('Editing condition %label', array('%label' => $label)));
  }
  $form['negate'] = array(
    '#title' => t('Negate'),
    '#type' => 'checkbox',
    '#description' => t('If checked, the condition returns TRUE, if it evaluates to FALSE.'),
    '#default_value' => isset($element['#negate']) ? $element['#negate'] : 0,
  );
  $form['weight'] = array(
    '#title' => t('Weight'),
    '#type' => 'weight',
    '#description' => t('Adjust the weight to customize the ordering of conditions.'),
    '#default_value' => isset($element['#weight']) ? $element['#weight'] : 0,
    '#weight' => 8,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#weight' => 10,
    '#value' => t('Save'),
    '#submit' => array('rules_admin_form_edit_condition_submit', 'rules_admin_form_edit_save'),
  );
  if (isset($element['#id'])) {
    $form['delete'] = array(
      '#type' => 'submit',
      '#weight' => 11,
      '#value' => t('Delete'),
      '#submit' => array('rules_admin_form_edit_delete_submit'),
    );
  }
  else {
    // For conditions we set the id to 0, so new variables of this rules aren't shown
    // in argument mapping forms.
    $element['#id'] = 0;
  }
  rules_admin_element_help($form, $element);
  rules_admin_default_argument_form($form, $form_state, $element);
  rules_admin_element_alter_form($form, $form_state, $element);
  return $form;
}

/**
 * Apply the changes to the element
 */
function rules_admin_form_edit_condition_submit($form, &$form_state) {
  $element =& $form_state['element'];
  foreach (array('negate', 'weight') as $key) {
    $element['#'. $key] = $form_state['values'][$key];
  }
  rules_admin_default_argument_form_submit($form, $form_state, $element);
  rules_admin_save_element_label($form, $form_state, $element);
  rules_admin_element_alter_form_submit($form, $form_state, $element);
  drupal_set_message(t('The condition %label has been saved.', array('%label' => rules_get_element_label($element))));
}

/**
 * Returns the edit form of a logical operation
 */
function rules_admin_form_edit_op(&$form_state, $element) {
  drupal_set_title(t('Editing condition group %label', array('%label' => rules_get_element_label($element))));
  $form['negate'] = array(
    '#title' => t('Negate'),
    '#type' => 'checkbox',
    '#description' => t('If checked, the operation will be negated. E.g. AND would be handled as NOT AND.'),
    '#default_value' => isset($element['#negate']) ? $element['#negate'] : 0,
  );
  $form['operation'] = array(
    '#title' => t('Operation'),
    '#type' => 'select',
    '#description' => t('The logical operation of this condition group. E.g. if you select AND, this condition group will only evaluate to TRUE if all conditions of this group evaluate to TRUE.'),
    '#default_value' => $element['#type'],
    '#options' => rules_admin_elements_get_logical_ops(),
    '#required' => TRUE,
  );
  $form['weight'] = array(
    '#title' => t('Weight'),
    '#type' => 'weight',
    '#description' => t('Adjust the weight to customize the ordering.'),
    '#default_value' => isset($element['#weight']) ? $element['#weight'] : 0,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#weight' => 10,
    '#value' => t('Save'),
    '#submit' => array('rules_admin_form_edit_op_submit', 'rules_admin_form_edit_save'),
  );
  if (isset($element['#id'])) {
    $form['delete'] = array(
      '#type' => 'submit',
      '#weight' => 10,
      '#value' => t('Delete'),
      '#submit' => array('rules_admin_form_edit_delete_submit'),
    );
  }
  return $form;
}

/**
 * Apply the changes to the element
 */
function rules_admin_form_edit_op_submit($form, &$form_state) {
  $element =& $form_state['element'];
  foreach (array('negate', 'weight') as $key) {
    $element['#'. $key] = $form_state['values'][$key];
  }
  $element['#type'] = $form_state['values']['operation'];
  drupal_set_message(t('The condition group %label has been saved.', array('%label' => $element['#type'])));
}

/**
 * Actually saves the element. Note that this handles also saving newly added elements.
 */
function rules_admin_form_edit_save($form, &$form_state) {
  $element =& $form_state['element'];
  //remove empty values
  $element = array_filter($element);

  if (isset($element['#id']) && $element['#id']) {
    //save edited element
    $element_ref =& $form_state['proxy']->get_element($element['#id']);
    $element_ref = $element;
    unset($element_ref['#id']);
  }
  else {
    $parent_ref =& $form_state['proxy']->get_element($form_state['parent_id']);
    //just add the element to the parent
    unset($element['#id']);
    $parent_ref = rules_configure($parent_ref, $element);
  }
  $form_state['proxy']->save_changes();
  $form_state['redirect'] = RULES_ADMIN_RULE_PATH .'/'. $form_state['proxy']->get_rule_name();
  unset($form_state['storage']);
}

/**
 * Just redirect to the delete form
 */
function rules_admin_form_edit_delete_submit($form, &$form_state) {
  $element =& $form_state['element'];
  $base_path = RULES_ADMIN_RULE_PATH .'/'. $form_state['proxy']->get_rule_name();
  unset($_REQUEST['destination']);
  $form_state['redirect'] = array(
    'path' => $base_path . '/delete/'. $element['#id'],
    'query' => 'destination='. $base_path,
  );
  // We need to empty the storage or there will be no redirect.
  $form_state['storage'] = NULL;
}

/**
 * Allows the element to alter the default configuration form
 */
function rules_admin_element_alter_form(&$form, &$form_state, $element) {
  rules_include('rules_forms');
  $element += array('#settings' => array());

  rules_element_invoke($element, 'form', array($element['#settings'], &$form, &$form_state), FALSE);
  $form['submit']['#validate'][] = 'rules_admin_element_alter_form_validate';
}

function rules_admin_element_alter_form_validate(&$form, &$form_state) {
  rules_include('rules_forms');
  rules_element_invoke($form_state['element'], 'validate', array(&$form, &$form_state), FALSE);
}

function rules_admin_element_alter_form_submit(&$form, &$form_state, &$element) {
  rules_include('rules_forms');
  rules_element_invoke($element, 'submit', array(&$element['#settings'], &$form, &$form_state), FALSE);
}

/**
 * Returns form elements for new variables
 */
function rules_admin_new_variables_form(&$form, &$form_state, $element) {
  $info = rules_get_element_info($element);

  if (isset($info['new variables']) && count($info['new variables'])) {
    $form['new'] = array('#tree' => TRUE);
    foreach ($info['new variables'] as $name => $var) {
      $form['new'][$name] = array(
        '#type' => 'fieldset',
        '#title' => t('Variable @label settings', array('@label' => $var['label'])),
        '#collapsible' => TRUE,
      );
      $form['new'][$name]['label'] = array(
        '#type' => 'textfield',
        '#title' => t('Label'),
        '#default_value' => $var['label'],
        '#required' => TRUE,
      );
      $form['new'][$name]['name'] = array(
        '#type' => 'textfield',
        '#title' => t('Machine readable variable name'),
        '#description' => t('Specify a unique name containing only alphanumeric characters, and underscores.'),
        '#default_value' => $name,
        '#required' => TRUE,
      );
      //preserve its old argument map value
      $default_name = isset($element['#settings']['#argument map'][$name]) ? $element['#settings']['#argument map'][$name] : $name;
      $form['map'][$default_name] = array('#type' => 'value', '#value' => $name);
    }
    $form['submit']['#validate'][] = 'rules_admin_new_variables_form_validate';
  }
}

function rules_admin_new_variables_form_validate($form, &$form_state) {
  $variables = $form_state['proxy']->get_defined_variables($form_state['element']['#id']);
  foreach ($form_state['values']['new'] as $name => $values) {
    if (isset($variables[ $values['name'] ])) {
      form_set_error(implode('][', array('new', $name, 'name')), t('A variable with this name does already exist. Please choose another name.'));
    }
    if (!eregi('^[0-9a-z_]*$', $values['name'])) {
      form_set_error(implode('][', array('new', $name, 'name')), t('The name contains not allowed characters.'));
    }
  }
}

function rules_admin_new_variables_form_submit(&$form, &$form_state, &$element) {
  if (isset($form_state['values']['new'])) {
    foreach ($form_state['values']['new'] as $old_name => $values) {

      //handle the label
      $info = &$element['#info']['new variables'][$old_name];
      $info = _rules_admin_get_label($form_state, $info, $element, $values['label']) + $info;

      if ($old_name != $values['name']) {
        //add it to the argument map
        $default_name = $element['#settings']['#argument map'][$old_name];
        $element['#settings']['#argument map'][ $values['name'] ] = $default_name;
        unset($element['#settings']['#argument map'][$old_name]);
        //and alter the info about the variable to reflect the name change
        $element['#info']['new variables'][ $values['name'] ] = $element['#info']['new variables'][$old_name];
        unset($element['#info']['new variables'][$old_name]);
      }
    }
  }
}

/**
 * Returns the argument form for the given element
 */
function rules_admin_default_argument_form(&$form, &$form_state, $element) {
  $form['input_help'] = rules_input_evaluators_help($element, $form_state['proxy']->get_available_variables($form_state['element']['#id']));
  $form['settings'] = array('#tree' => TRUE);
  $form['map'] = array('#tree' => TRUE);

  $function = $element['#name']. '_form';
  $info = rules_get_element_info($element);

  if (isset($info['arguments']) && count($info['arguments'])) {
    $variables = $form_state['proxy']->get_available_variables($element['#id']);

    foreach ($info['arguments'] as $name => $arg_info) {
      $arg = rules_get_data_object($arg_info);
      if (!$arg->is_identifiable()) {
        $value = isset($element['#settings'][$name]) ? $element['#settings'][$name] : NULL;
        $form['settings'][$name] = $arg->get_default_input_form($arg_info, $value);
      }
      else {
        $args = rules_admin_map_get_possible_arguments($arg_info, $variables);
        $form['map'][$name] = array(
          '#type' => 'select',
          '#title' => $arg_info['label'],
          '#options' => $args,
          '#default_value' => rules_admin_map_get_configured_argument($name, $element),
          '#description' => isset($arg_info['description']) ? $arg_info['description'] : '',
        );
      }
    }
    if (element_children($form['map'])) {
      $form['map'] += array(
        '#type' => 'fieldset',
        '#title' => t('Arguments configuration'),
        '#weight' => -2,
      );
    }
  }
}

function rules_admin_default_argument_form_submit($form, &$form_state, &$element) {
  //save the argument map
  $element['#settings'] = array();
  if (isset($form_state['values']['map'])) {
    $element['#settings']['#argument map'] = array_flip($form_state['values']['map']);
  }
  //add in values of not identifiable variables
  if (isset($form_state['values']['settings'])) {
    $element['#settings'] += $form_state['values']['settings'];
  }
  rules_prepare_input_evaluators($element, $form_state['proxy']->get_available_variables($form_state['element']['#id']));
}

/*
 * Gets the in the argument map configured argument for the given argument - or the default value
 */
function rules_admin_map_get_configured_argument($name, $element) {
  if (!isset($element['#settings']['#argument map'])) {
    //per default try the the same name
    return $name;
  }
  $reverse_map = array_flip($element['#settings']['#argument map']);
  return isset($reverse_map[$name]) ? $reverse_map[$name] : $name;
}

/**
 * Shows the element help
 */
function rules_admin_element_help(&$form, $element) {
  $info = rules_get_element_info($element);
  if (isset($info['help']) || isset($element['help'])) {
    $help = isset($element['help']) ? t($element['help']) : t($info['help']);
  }
  else if (isset($element['#type']) && in_array($element['#type'], array('action', 'condition'))) {
    rules_include('rules_forms');
    $help = rules_element_invoke($element, 'help', array());
  }
  if (isset($help) && $help) {
    $form['help'] = array(
      '#type' => 'fieldset',
      '#title' => t('Description'),
      '#description' => $help,
    );
  }
}

/**
 * Shows the delete form for elements (conditions, actions, ..)
 */
function rules_admin_form_delete(&$form_state, $proxy, $element) {
  $form_state['proxy'] = &$proxy;

  //get the item assocaited with $id
  $form_state['id'] = $element['#id'];
  _rules_element_defaults($element);

  $form = array();
  $path = array();
  $path['path'] = isset($_GET['destination']) ? $_GET['destination'] : RULES_ADMIN_PATH;

  if (isset($element['#logical_op']) && $element['#logical_op']) {
    $form_state['is_op'] = TRUE;
    $text = t('Are you sure you want to delete the logical operation %label?', array('%label' => rules_get_element_label($element)));
  }
  else {
    $text = t('Are you sure you want to delete %label?', array('%label' => rules_get_element_label($element)));
  }
  return confirm_form($form, $text, $path, t('This action cannot be undone.'), t('Delete'), t('Cancel'));
}

function rules_admin_form_delete_submit($form_id, $form_state) {
  $rule = $form_state['proxy']->get_rule();
  //get a reference on the element
  $element = &$form_state['proxy']->get_element(intval($form_state['id']));

  if (isset($form_state['is_op']) && $form_state['is_op']) {
    drupal_set_message(t("The logical operation %label has been deleted.", array('%label' => rules_get_element_label($element))));
    //just unset the type, so that containing conditions are not deleted
    unset($element['#type']);
  }
  else {
    drupal_set_message(t("%label has been deleted.", array('%label' => rules_get_element_label($element))));
    $element = NULL;
    //remove the elements key by cleaning the rule
  }
  $form_state['proxy']->clean_rule();
  $form_state['proxy']->save_changes();
  $form_state['redirect'] = RULES_ADMIN_PATH;
}

/**
 * Clones a rule
 */
function rules_admin_form_clone(&$form_state, $proxy) {

  $form_state['proxy'] = &$proxy;
  $rule = $proxy->get_rule();
  _rules_element_defaults($rule);

  if (!$form_state['post']) {
    drupal_set_message(t('Alter the settings for the cloned rule.'));
  }

  $form_state['set'] = strpos($rule['#set'], 'event_') !== 0;
  $form = rules_admin_form_rule_settings($rule, $form_state['set']);
  $form['#submit'] = array('rules_admin_form_add_rule_submit');
  return $form;
}

/**
 * Gets all compatible sets, which means that all arguments are available in the other set too
 */
function rules_admin_get_compatible_sets($rule, $sets) {
  if (isset($rule['#set']) && $rule['#set']) {
    $set = rules_get_rule_sets($rule['#set']);
    foreach ($sets as $key => $this_set) {
      if (array_diff(array_keys($set['arguments']), array_keys($this_set['arguments']))) {
        unset($sets[$key]);
      }
    }
  }
  return $sets;
}

/**
 * Gets an array of element labels grouped by modules
 *
 * @param $elements
 *   An array of info elements, e.g. as returned from rules_get_events().
 * @param $key
 *   The key for which the value is used for grouping the elements.
 */
function rules_admin_get_grouped_labels($elements, $key = 'module') {
  $grouped = array();
  $ungrouped = array();

  foreach (array_filter($elements, 'rules_admin_element_filter') as $name => $element) {
    if (isset($element[$key])) {
      $grouped[$element[$key]][$name] = rules_get_element_label($element);
    }
    else {
      $ungrouped[$name] = rules_get_element_label($element);
    }
  }
  foreach ($grouped as $name => $group) {
    asort($grouped[$name]);
  }
  ksort($grouped);
  asort($ungrouped);
  return $ungrouped + $grouped;
}

/**
 * This is used for filtering the rules, action and conditions that should be shown.
 * Rules and sets are filtered when they are fixed and the variable 'rules_show_fixed'
 * is  not set to on. Actions and conditions are filtered out if they are marked as
 * being hidden.
 *
 * @return TRUE, when the element should be kept. Else FALSE.
 */
function rules_admin_element_filter($element) {
  if (!variable_get('rules_show_fixed', FALSE)) {
    if (isset($element['#status']) && $element['#status'] == 'fixed') {
      return FALSE;
    }
    if (isset($element['status']) && $element['status'] == 'fixed') {
      return FALSE;
    }
  }
  if (isset($element['hidden']) && $element['hidden']) {
    return FALSE;
  }
  return TRUE;
}

/**
 * Determines if the given data type is valid for the given argument.
 *
 * @param $data_type
 *   The data type to check.
 * @param $arg_type
 *   The arguments needed data type.
 * @return
 *   True or false.
 */
function rules_admin_is_valid_data_type($data_type, $arg_type) {
  if (is_array($arg_type)) {
    foreach ($arg_type as $type) {
      // We have multiple options to check for.
      if (rules_admin_is_valid_data_type($data_type, $type)) {
        return TRUE;
      }
    }
    return FALSE;
  }
  if ($arg_type == '*') {
    return TRUE;
  }
  return $arg_type == $data_type;
}


/**
 * Determines which operation should be added
 * If the parent operation is an AND, we add an OR, and vice versa.
 */
function rules_admin_determine_operation(&$proxy, $id) {
  $parent = $proxy->get_element($proxy->get_element_parent_id($id)) + array('#type' => '');
  switch ($parent['#type']) {
    default:
    case 'rule':
    case 'AND':
      return 'OR';
    case 'OR':
      return 'AND';
  }
}

/**
 * Returns a list of available logical operations suitable for use with #options
 */
function rules_admin_elements_get_logical_ops() {
  $elements = rules_gather_data('elements');
  $labels = array_map('rules_get_element_label', array_filter($elements, '_rules_admin_element_is_logical_op'));
  asort($labels);
  return $labels;
}

function _rules_admin_element_is_logical_op($element) {
  return isset($element['#logical_op']) && $element['#logical_op'];
}

/**
 * Filters the info about elements (actions, conditions), so that only elements
 * compatible with the given variables are kept, which means suiting variables for all arguments
 * are available, so that the action/condition can be configured
 */
function rules_admin_filter_info($variables, $infos) {
  $filtered = array();
  foreach ($infos as $key => $info) {
    if (isset($info['arguments']) && is_array($info['arguments'])) {
      foreach ($info['arguments'] as $name => $argument_info) {
        if (!rules_admin_argument_satisifable($argument_info, $variables)) {
          // Skip this element
          continue 2;
        }
      }
      $filtered[$key] = $info;
    }
    else {
      $filtered[$key] = $info;
    }
  }
  return $filtered;
}

/**
 * Determines whether the argument can be configured properly, so whether
 * there are matching variables available or needed.
 */
function rules_admin_argument_satisifable($info, $variables) {
  if ($info['type'] == '*') {
    return TRUE;
  }
  if (($type_info = rules_get_data_types($info['type']))) {
    if (isset($type_info['identifiable']) && !$type_info['identifiable']) {
      // Argument value will be set on configuration time.
      return TRUE;
    }
  }
  if (count(rules_admin_map_get_possible_arguments($info, $variables))) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Gets the possible arguments (= of the same entity) for an argument
 */
function rules_admin_map_get_possible_arguments($arg_info, $variables) {
  $matches = array();
  foreach ($variables as $name => $info) {
    if (rules_admin_is_valid_data_type($info['type'], $arg_info)) {
      $matches[$name] = $info['label'];
    }
  }
  return $matches;
}

/**
 * Returns an array of all existing categories
 */
function rules_admin_get_categories() {
  $categories = array();
  foreach (rules_get_configured_items('rules') as $rule) {
    if (isset($rule['#categories']) && is_array($rule['#categories']) && rules_admin_element_filter($rule)) {
      foreach ($rule['#categories'] as $category) {
        if (!in_array($category, $categories)) {
          $categories[$category] = $category;
        }
      }
    }
  }
  return $categories;
}

function _rules_admin_rule_get_new_unique_name() {
  $result = db_query("SELECT COUNT(name) FROM {rules_rules} WHERE name LIKE 'rules_%%'");
  return 'rules_'. (db_result($result) + 1);
}

/**
 * Saves the element label.
 */
function rules_admin_save_element_label($form, &$form_state, &$element) {
  $info = _rules_admin_get_label($form_state, rules_get_element_info($element), $element, $form_state['values']['label']);
  //this makes sure we don't save the applied defaults
  $element['#info'] = $info + $element['#info'];
}

/**
 * Gets the label for the info of the given element by apply label callbacks.
 * Note that this is also used for argument infos.
 *
 * @return An array of changed properties of the given info
 */
function _rules_admin_get_label(&$form_state, $info, &$element, $value) {
  $info_changes = array();
  if ($info['label'] != $value) {
    //label has been customized
    $info_changes['label'] = $value;
    $info_changes['label callback'] = FALSE;
  }
  else if (isset($info['label callback']) && $info['label callback'] && function_exists($info['label callback'])) {
    $argument_labels = rules_admin_get_argument_labels($form_state['proxy'], $element);
    $info_changes['label'] = $info['label callback']($element['#settings'], $argument_labels, $element);
  }
  return $info_changes;
}


/**
 * Gets the labels of the variables, which are configured to be passed as arguments
 * to the given element.
 */
function rules_admin_get_argument_labels($proxy, $element, $prefix = '@') {
  $labels = array();
  $names = rules_get_mapped_argument_names($element);
  $vars = $proxy->get_available_variables();
  $labels = array();
  foreach ($names as $argument_name => $variable_name) {
    if (isset($vars[$variable_name]['label'])) {
      $labels[$prefix . $argument_name] = $vars[$variable_name]['label'];
    }
  }
  return $labels;
}

/**
 * Defines the rules settings form
 */
function rules_admin_settings(&$form_state) {
  $form['rules_debug'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debug rule evaluation'),
    '#default_value' => variable_get('rules_debug', FALSE),
    '#description' => t('When activated, debugging information is shown when rules are evaluated.'),
  );
  $form['rules_show_fixed'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show fixed rules and rule sets'),
    '#default_value' => variable_get('rules_show_fixed', FALSE),
    '#description' => t('When activated, fixed items provided by modules are shown in the admin center too.'),
  );
  $form['rules_hide_token_message'] = array(
    '#type' => 'checkbox',
    '#title' => t('Ignore missing token module'),
    '#default_value' => variable_get('rules_hide_token_message', FALSE),
    '#description' => t('Rules can use the token module to provide token replacements; if this module is not present rules will complain, unless this setting is checked.'),
  );
  return system_settings_form($form);
}


/**
 * Fixes the breadcrumb on the rule edit/delete/revert
 */
function rules_admin_fix_breadcrumb($is_set = FALSE, $set_name, $set_info) {
  $breadcrumb = drupal_get_breadcrumb();
  $item = menu_get_item($is_set ? RULES_ADMIN_SET_PATH : RULES_ADMIN_TRIGGER_PATH);
  $breadcrumb[] = l($item['title'], $item['href'], $item['localized_options']);
  if ($is_set) {
    $breadcrumb[] = l($set_info['label'], RULES_ADMIN_SET_PATH .'/'. $set_name);
  }
  drupal_set_breadcrumb($breadcrumb);
}

