<?php
// $Id$

/**
 * @file Provides export functionality and integrates with the features module.
 */

/**
 * Export all items given to code, where $export has to be an array of arrays
 * keyed by item types, containing the items keyed by their names.
 */
function rules_export_items($export) {
  foreach (array_keys($export) as $item_type) {
    // Allow item specific adaption before exporting
    foreach ($export[$item_type] as $item_name => $item) {
      rules_item_type_invoke($item_type, 'export', array($item_name, &$export[$item_type][$item_name], &$export));
    }
  }
  return var_export($export, TRUE);
}

/**
 * Item type callback: When exporting a rule set, add its rules to the export.
 */
function rules_item_rule_set_export($set_name, &$rule_set, &$export) {
  $rules = rules_get_configured_items('rules');
  foreach ($rules as $name => $rule) {
    if ($rule['#set'] == $set_name) {
      $export['rules'][$name] = $rule;
    }
  }
}
