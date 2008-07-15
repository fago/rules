<?php
// $Id$

/**
 * @file Rules Sets Admin UI
 */

require_once drupal_get_path('module', 'rules') . '/rules.admin.inc';



function rules_admin_sets_overview() {
  $sets = rules_get_configured_items('rule_sets');
  $sets = array_filter($sets, 'rules_admin_element_filter');
  ksort($sets);

  $header = array(t('Label'), t('Category'), t('Status'), t('Operations'));
  $rows = array();

  foreach ($sets as $name => $set_info) {
    $set_info += array('status' => 'altered');

    $path = RULES_ADMIN_SET_PATH .'/'. $name;
    $ops = array();
    if ($set_info['status'] != 'fixed') {
      $ops[] = l(t('edit'), $path .'/edit');
    }
    if ($set_info['status'] == 'custom') {
      $ops[] = l(t('delete'), $path .'/delete', array('query' => drupal_get_destination()));
    }
    else if ($set_info['status'] == 'altered') {
      $ops[] = l(t('revert'), $path .'/revert', array('query' => drupal_get_destination()));
    }
    $categories = isset($set_info['categories']) ? array_map('check_plain', $set_info['categories']) : array();

    $rows[] = array(
      l($set_info['label'], $path .'/edit'),
      implode(', ', $categories),
      rules_admin_configuration_status($set_info['status']),
      implode(' ', $ops),
    );
  }

  if (count($rows)) {
    return theme('table', $header, $rows, array('class' => 'rules-sets-configurations'));
  }

  return '<p>'. t('None') .'</p>';
}

/**
 * Shows the delete form for items
 */
function rules_admin_item_form_delete(&$form_state, $item_type, $item) {
  $form = array();
  $form_state['item'] = $item;
  $form_state['item_type'] = $item_type;

  $path = array();
  $path['path'] = isset($_GET['destination']) ? $_GET['destination'] : RULES_ADMIN_PATH;

  return confirm_form($form,
    t('Are you sure you want to delete %label?', array('%label' => rules_get_element_label($item))),
    $path,
    t('This action cannot be undone.'), t('Delete'), t('Cancel')
  );
}

function rules_admin_item_form_delete_submit($form, &$form_state) {
  rules_item_delete($form_state['item_type'], $form_state['item']['name']);
  rules_clear_cache();
  drupal_set_message(t("%label has been deleted.", array('%label' => rules_get_element_label($form_state['item']))));
  $form_state['redirect'] = RULES_ADMIN_PATH;
}

/**
 * Shows the revert form for items
 */
function rules_admin_item_form_revert(&$form_state, $item_type, $item) {
  $form = array();
  $form_state['item'] = $item;
  $form_state['item_type'] = $item_type;

  $path = array();
  $path['path'] = isset($_GET['destination']) ? $_GET['destination'] : RULES_ADMIN_PATH;

  return confirm_form($form,
    t('Are you sure you want to revert %label?', array('%label' => rules_get_element_label($item))),
    $path,
    t('This action cannot be undone.'), t('Revert'), t('Cancel')
  );
}

function rules_admin_item_form_revert_submit($form, &$form_state) {
  rules_item_delete($form_state['item_type'], $form_state['item']['name']);
  rules_clear_cache();
  drupal_set_message(t("%label has been reverted.", array('%label' => rules_get_element_label($form_state['item']))));
  $form_state['redirect'] = RULES_ADMIN_PATH;
}

/**
 * Item type callback: Deleted rule set
 */
function rules_item_rule_set_delete($set_name) {
  //delete all rules belonging to this set too
  $rules = rules_get_configured_items('rules');
  foreach ($rules as $name => $rule) {
    if ($rule['#set'] == $set_name) {
      rules_item_delete('rules', $name);
    }
  }
}
