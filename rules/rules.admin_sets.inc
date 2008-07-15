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
    $set_info += array('status' => 'custom');

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
