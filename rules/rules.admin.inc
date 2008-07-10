<?php

require_once drupal_get_path('module', 'rules') . '/rules.admin_rule_proxy.inc';


/**
 * Lists the available rules.
 */
function rules_admin_form_overview(&$form_state) {
  $form['filter'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter'),
    '#collapsible' => TRUE,
    '#collapsed' => isset($form_state['values']),
  );
  $form['filter']['set'] = array(
    '#type' => 'select',
    '#title' => t('Filter by event'),
    '#options' => array(0 => '<All>') + rules_get_grouped_labels(rules_get_events()),
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
  _rules_sort_children($rules);

  //set parameter defaults
  $params += array('category' => NULL, 'set' => NULL, 'active' => TRUE, 'fixed' => FALSE);
  extract($params);

  $header = array(t('Label'), t('Event'), t('Category'), t('Status'), t('Operations'));
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
        rules_admin_configuration_status($rule),
        implode(' ', $ops),
      );
    }
  }

  if (count($rows)) {
    return array('#value' => theme('table', $header, $rows, array('class' => 'rules-ng-configurations')));
  }

  return array('#value' => t('None.'));
}

/**
 * Returns the status of the configuration as html.
 */
function rules_admin_configuration_status($rule) {
  if ($rule['#status'] == 'altered') {
    $help = t('This rule has been provided by a module, but has been modified.');
    return '<span title="'. $help .'">'. t('Modified'). '</span>';
  }
  else if ($rule['#status'] == 'fixed') {
    $help = t('This rule has been provided by a module and can\'t be customized.');
    return '<span title="'. $help .'">'. t('Fixed') .'</span>';
  }
  else if ($rule['#status'] == 'custom') {
    $help = t('A custom defined rule.');
    return '<span title="'. $help .'">'. t('Custom'). '</span>';
  }
  $help = t('This rule has been provided by a module.');
  return '<span title="'. $help .'">'. t('Default') .'</span>';
}

/**
 * Returns the add rule form
 */
function rules_admin_form_add_rule(&$form_state) {
  $form_state['set'] = arg(2) != 'trigger';
  return rules_admin_form_rule_settings(array(), $form_state['set']);
}

function rules_admin_form_add_rule_submit($form_id, &$form_state) {
  $rule = isset($form_state['proxy']) ? $form_state['proxy']->get_rule() : array('#type' => 'rule');

  foreach (array('set', 'label', 'active', 'weight') as $key) {
    $rule['#'. $key] = $form_state['values'][$key];
  }
  $rule['#status'] = 'custom';

  //get an unique name
  $result = db_query("SELECT COUNT(name) FROM {rules_rules} WHERE name LIKE 'rules_%%'");
  $rule['#name'] = 'rules_'. (db_result($result) + 1);

  rules_item_save('rules', $rule['#name'], $rule);
  rules_clear_cache();
  drupal_set_message(t("The rule %label has been added.", array('%label' => $rule['#label'])));
  $form_state['redirect'] =  RULES_ADMIN_RULE_PATH .'/'. $rule['#name'];
}

/**
 * Returns the form for the settings of a rule
 */
function rules_admin_form_rule_settings($rule, $set = FALSE) {
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
  if ($set) {
    $form['settings']['set'] += array(
      '#title' =>t('Rule set'),
    	'#options' => rules_get_grouped_labels(rules_admin_get_compatible_sets($rule, rules_get_configured_items('rule_sets'))),
    	'#description' => t('Select to which rule set this rule should belong.'),
    );
  }
  else {
    $form['settings']['set'] += array(
      '#title' => t('Event'),
    	'#options' => rules_get_grouped_labels(rules_admin_get_compatible_sets($rule, rules_get_event_sets())),
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

  $form = rules_admin_form_rule_settings($rule);

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
 * Shows the revert configuration form
 */
function rules_admin_form_revert(&$form_state, $proxy) {
  $form_state['proxy'] = &$proxy;
  $rule = $proxy->get_rule();
  _rules_element_defaults($rule);

  $form = array();
  $path['path'] = isset($_GET['destination']) ? $_GET['destination'] : RULES_ADMIN_PATH;

  return confirm_form($form,
    t('Are you sure you want to revert the rule %label to its defaults?', array('%label' => $rule['#label'])),
    $path,
    t('This action cannot be undone.'), t('Revert'), t('Cancel')
  );
}

function rules_admin_form_revert_submit($form_id, $form_state) {
  $rule = $form_state['proxy']->get_rule();
  $form_state['proxy']->delete_rule();
  rules_clear_cache();
  drupal_set_message(t("The rule %label has been reverted to its defaults.", array('%label' => $rule['#label'])));
  $form_state['redirect'] = RULES_ADMIN_PATH;
}

/*
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

    //get the item assocaited with $id
    if ($form_state['step'] == 0) {
      if (isset($parent_id) && is_array($parent = $proxy->get_element(intval($parent_id)))) {
        if (function_exists($function = 'rules_admin_form_add_'. $element['#type'])) {
          $form_state['step']++;
          $form_state['parent_id'] = intval($parent_id);
          return $function($form_state, $element, $parent);
        }
      }
    }
    else {
      if (function_exists($function = 'rules_admin_form_edit_'. $type)) {
        $form_state['step']++;
        return $function($form_state, $element);
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

  $actions = rules_admin_filter_elements($vars, rules_get_actions());
  $form['name'] = array(
    '#type' => 'select',
    '#title' => t('Select an action to add'),
    '#options' => rules_get_grouped_labels($actions),
    '#required' => TRUE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#weight' => 10,
    '#value' => t('Forward'),
    '#submit' => array('rules_admin_form_add_submit', 'rules_admin_form_pack_storage'),
  );
  return $form;
}

/**
 * Returns the form for the first condition add page
 */
function rules_admin_form_add_condition(&$form_state, &$element, $parent) {
  $info = rules_get_element_info($parent);

  if (!isset($parent['#type']) || isset($info['#logical_op']) && $info['#logical_op']) {
    $vars = $form_state['proxy']->get_available_variables(0);
    $conditions = rules_admin_filter_elements($vars, rules_get_conditions());
    $form['name'] = array(
      '#type' => 'select',
      '#title' => t('Select the condition to add'),
      '#options' => rules_get_grouped_labels($conditions),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#weight' => 10,
      '#value' => t('Forward'),
      '#submit' => array('rules_admin_form_add_submit', 'rules_admin_form_pack_storage'),
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
  $op = rules_determine_operation($form_state['proxy'], $form_state['parent_id']);
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
  rules_get_element_info($form_state['element']);
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
}

/**
 * Use this when the current form is or might be not the first step of the form.
 * It puts the variables out of storage on their usual place.
 * Note: We can't use the storage place everywhere, as it would result in unnecessary form rebuilds
 *   when building the form initially.
 */
function rules_admin_form_unpack_storage($form, &$form_state) {
  foreach (array('proxy', 'step', 'element', 'parent_id') as $key) {
    if (isset($form_state['storage'][$key])) {
      $form_state[$key] =& $form_state['storage'][$key];
    }
  }
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
    '#description' => t('Choose an appropriate label for this action.'),
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
    '#validate' => array('rules_admin_form_unpack_storage'),
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
  foreach (array('label', 'weight') as $key) {
    $element['#'. $key] = $form_state['values'][$key];
  }
  rules_admin_default_argument_form_submit($form, $form_state, $element);
  rules_admin_new_variables_form_submit($form, $form_state, $element);
  rules_admin_element_alter_form_submit($form, $form_state, $element);

  drupal_set_message(t('The action %label has been saved.', array('%label' => $element['#label'])));
}


/**
 * Returns the edit form for a condition
 */
function rules_admin_form_edit_condition(&$form_state, $element) {
  $label = rules_get_element_label($element);
  $form['label'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#description' => t('Choose an appropriate label for this condition.'),
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
    '#validate' => array('rules_admin_form_unpack_storage'),
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
  foreach (array('label', 'negate', 'weight') as $key) {
    $element['#'. $key] = $form_state['values'][$key];
  }
  rules_admin_default_argument_form_submit($form, $form_state, $element);
  rules_admin_element_alter_form_submit($form, $form_state, $element);
  drupal_set_message(t('The condition %label has been saved.', array('%label' => $element['#label'])));
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
    '#options' => rules_elements_get_logical_ops(),
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

  if (isset($element['#id'])) {
    //save edited element
    $element_ref =& $form_state['proxy']->get_element($element['#id']);
    $element_ref = $element;
    unset($element_ref['#id']);
  }
  else {
    $parent_ref =& $form_state['proxy']->get_element($form_state['parent_id']);
    //just add the element to the parent
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
}

/**
 * Allows the element to alter the default configuration form
 */
function rules_admin_element_alter_form(&$form, &$form_state, $element) {
  rules_include('rules_forms');
  $function = $element['#name']. '_form';

  if (function_exists($function)) {
    $element += array('#settings' => array());
    $function($element['#settings'], $form, $form_state);
    $form['#validate'][] = $element['#name']. '_validate';
  }
}

function rules_admin_element_alter_form_submit(&$form, &$form_state, $element) {
  rules_include('rules_forms');
  $function = $element['#name']. '_submit';

  if (function_exists($function)) {
    $function($element['#settings'], $form, $form_state);
  }
}

/**
 * Returns form elements for new variables
 */
function rules_admin_new_variables_form(&$form, &$form_state, $element) {
  rules_get_element_info($element);
  $info = $element['#info'];

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
        '#description' => t('Specify a unique name containing only digits, numbers and underscores.'),
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
      $element['#info']['new variables'][$old_name]['label'] = $values['label'];

      if ($old_name != $values['name']) {
        //add it to the argument map
        $default_name = $element['#settings']['#argument map'][$old_name];
        $element['#settings']['#argument map'][ $values['name'] ] = $default_name;
        unset($element['#settings']['#argument map'][$old_name]);
        //and alter the info about the variable
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
  $form['settings'] = array('#tree' => TRUE);
  $form['map'] = array('#tree' => TRUE);

  $function = $element['#name']. '_form';
  $info = rules_get_element_info($element);

  if (isset($info['arguments']) && count($info['arguments'])) {
    $form['map'] += array(
      '#type' => 'fieldset',
      '#title' => t('Arguments configuration'),
      '#weight' => -2,
    );
    $variables = $form_state['proxy']->get_available_variables($element['#id']);

    foreach ($info['arguments'] as $name => $arg_info) {
      $arg = rules_get_data_object($arg_info);
      if (!$arg->is_identifiable()) {
        $value = isset($element['#settings'][$name]) ? $element['#settings'][$name] : NULL;
        $form['settings'][$name] = $arg->get_default_input_form($arg_info, $value);
      }
      //fall back to an argument map
      if (empty($form['settings'][$name])) {
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
  //TODO: apply input filter preparation
}


/**
 * Gets the possible arguments (= of the same entity) for an argument
 */
function rules_admin_map_get_possible_arguments($arg_info, $variables, $print_error = TRUE) {
  $matches = array();
  foreach ($variables as $name => $info) {
    if (rules_is_valid_data_type($info['type'], $arg_info)) {
      $matches[$name] = $info['label'];
    }
  }
  if ($print_error && count($matches) == 0) {
    drupal_set_message(t('There is no suitable variable for the argument %name available.', array('%name', $info['label'])), 'error');
    drupal_goto(RULES_ADMIN_RULES_PATH);
  }
  return $matches;
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

/*
 * Shows the element description
 */
function rules_admin_element_help(&$form, $element) {
  $info = rules_get_element_info($element);
  if (isset($element['#help']) || isset($info['#help'])) {
    $form['help'] = array(
      '#type' => 'fieldset',
      '#title' => t('Configuration help'),
      '#description' => isset($element['#help']) ? $element['#help'] : $info['#help'],
    );
  }
}

/**
 * Shows the delete form for rules, conditions and actions
 */
function rules_admin_form_delete(&$form_state, $proxy, $element = NULL) {
  $form_state['proxy'] = &$proxy;
  $rule = $proxy->get_rule();
  _rules_element_defaults($rule);

  $form = array();
  $path = array();
  $path['path'] = isset($_GET['destination']) ? $_GET['destination'] : RULES_ADMIN_PATH;

  if (!isset($element)) {
    return confirm_form($form,
      t('Are you sure you want to delete the rule %label?', array('%label' => $rule['#label'])),
      $path,
      t('This action cannot be undone.'), t('Delete'), t('Cancel')
    );
  }
  else {
    //get the item assocaited with $id
    $form_state['id'] = $element['#id'];
    _rules_element_defaults($element);
    if (isset($element['#logical_op']) && $element['#logical_op']) {
      $form_state['is_op'] = TRUE;
      $text = t('Are you sure you want to delete the logical operation %label?', array('%label' => rules_get_element_label($element)));
    }
    else {
      $text = t('Are you sure you want to delete %label?', array('%label' => rules_get_element_label($element)));
    }
    return confirm_form($form, $text, $path, t('This action cannot be undone.'), t('Delete'), t('Cancel'));
  }
}

function rules_admin_form_delete_submit($form_id, $form_state) {
  $rule = $form_state['proxy']->get_rule();
  if (!isset($form_state['id'])) {
    $form_state['proxy']->delete_rule();
    rules_clear_cache();
    drupal_set_message(t("The rule %label has been deleted.", array('%label' => $rule['#label'])));
  }
  else {
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
  }
  $form_state['redirect'] = RULES_ADMIN_PATH;
}

/**
 * Clones a rule
 */
function rules_admin_form_clone(&$form_state, $proxy) {

  $form_state['proxy'] = &$proxy;
  $rule = $proxy->get_rule();
  _rules_element_defaults($rule);

  drupal_set_message(t('Alter the settings for the cloned rule.'));

  $form_state['set'] = strpos($rule['#set'], 'event_') !== 0;
  $form = rules_admin_form_rule_settings($rule, $form_state['set']);
  $form['#submit'] = array('rules_admin_form_configuration_add_submit');
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
 * @param $elements An array of info elements, e.g. as returned from rules_get_events()
 */
function rules_get_grouped_labels($elements) {
  $grouped = array();
  $ungrouped = array();

  foreach ($elements as $name => $element) {
    if (!isset($element['fixed']) || !$element['fixed']) {
      if (isset($element['module'])) {
        $grouped[$element['module']][$name] = $element['label'];
      }
      else {
        $ungrouped[$name] = $element['label'];
      }
    }
  }
  array_map('asort', $grouped);
  ksort($grouped);
  asort($ungrouped);
  return $ungrouped + $grouped;
}


/*
 * Determines which operation should be added
 * If the parent operation is an AND, we add an OR, and vice versa.
 */
function rules_determine_operation(&$proxy, $id) {
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

/*
 * Returns a list of available logical operations suitable for use with #options
 */
function rules_elements_get_logical_ops() {
  $elements = rules_gather_data('elements');
  $labels = array_map('rules_get_element_label', array_filter($elements, '_rules_element_is_logical_op'));
  asort($labels);
  return $labels;
}

function _rules_element_is_logical_op($element) {
  return isset($element['#logical_op']) && $element['#logical_op'];
}

/*
 * Imports a configuration
 */
function rules_admin_form_import() {
  $form = array();
  $form['import'] = array(
    '#type' => 'textarea',
    '#title' => t('Rule configurations to import'),
    '#description' => t('Paste exported rules here to import them.'),
    '#rows' => 15,
    '#required' => TRUE,
  );
  $form['button'] = array('#type' => 'submit', '#weight' => 10, '#value' => t('Import'));
  return $form;
}

function rules_admin_form_import_submit($form_id, $form_values) {
  @eval('$cfgs = '. $form_values['import'] .';');
  if (isset($cfgs) && is_array($cfgs) && count($cfgs)) {
    foreach ($cfgs as $name => $data) {
      if (strtolower($data['#module']) == 'rules-ng') {
        //generate a new unique name for this configuration
        $data['#name'] = 'cfg_'. db_next_id('{rules_cfg}_name');
      }
      rules_admin_save_rule($data);
      drupal_set_message(t('Imported rule configuration %label.', array('%label' => rules_get_element_label($data))));
    }
    rules_clear_cache();
  }
  else {
    drupal_set_message(t('Import failed.'), 'error');
  }
}


/*
 * Exports one ore more configurations
 */
function rules_admin_form_export($form_values = NULL) {
  $form = array();
  if (!isset($form_values)) {
    $form['cfg'] = array(
      '#type' => 'select',
      '#title' => t('Select the rule configurations to export'),
      '#options' => rules_get_grouped_labels(rules_get_configurations()),
      '#multiple' => TRUE,
    );
    $form['button'] = array('#type' => 'submit', '#weight' => 10, '#value' => t('Export'));
    $form['#multistep'] = TRUE;
    $form['#redirect'] = FALSE;
  }
  else {
    //show a textarea containg the exported configs
    $cfgs = array();
    foreach ($form_values['cfg'] as $key => $name) {
      $cfgs[$name] = rules_get_configurations('all', $name);
    }
    $form['result'] = array(
      '#type' => 'textarea',
      '#title' => t('Exported rule configurations'),
      '#description' => t('Copy these data and paste them into the import page, to import.'),
      '#rows' => 15,
      '#attributes' => array('readonly' => 'readonly'),
      '#default_value' => var_export($cfgs, TRUE),
    );
  }
  return $form;
}

/*
 * Filters the elements (actions, conditions), so that only elements
 * compatible with the event are kept, which means suiting variables for all arguments
 * are available, so that the action/condition can be configured
 */
function rules_admin_filter_elements($variables, $elements) {
  $filtered = array();
  foreach ($elements as $key => $element) {
    $info = rules_get_element_info($element) + array('arguments' => array());
    if (isset($info['arguments']) && is_array($info['arguments'])) {
      foreach ($info['arguments'] as $name => $info) {
        if (count(rules_admin_map_get_possible_arguments($info, $variables, FALSE)) == 0) {
          continue 2; //don't add this element to the filtered one
        }
      }
      $filtered[$key] = $element;
    }
  }
  return $filtered;
}

/**
 * Returns an array of all existing categories
 */
function rules_admin_get_categories() {
  $categories = array();
  foreach (rules_get_configured_items('rules') as $rule) {
    if (isset($rule['#categories']) && is_array($rule['#categories']) && (!isset($rule['#fixed']) || !$rule['#fixed'])) {
      foreach ($rule['#categories'] as $category) {
        if (!in_array($category, $categories)) {
          $categories[$element['#categories']] = $element['#categories'];
        }
      }
    }
  }
  return $categories;
}

