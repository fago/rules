<?php
// $Id$

/**
 * @file
 * Provides export functionality and integrates with the features module.
 */

/**
 * Export all items given, where $export has to be an array of arrays
 * keyed by item types, containing the items keyed by their names.
 */
function rules_export_items($export, $module = NULL) {
  foreach (array_keys($export) as $item_type) {
    // Allow item specific adaption before exporting
    foreach ($export[$item_type] as $item_name => $item) {
      rules_item_type_invoke($item_type, 'export', array($item_name, &$export[$item_type][$item_name], &$export, $module));
    }
  }
  return $export;
}

/**
 * Item type callback: When exporting a rule set, add its rules to the export.
 */
function rules_item_rule_set_export($set_name, &$rule_set, &$export, $module = NULL) {
  $rules = rules_get_configured_items('rules');
  foreach ($rules as $name => $rule) {
    if ($rule['#set'] == $set_name) {
      $export['rules'][$name] = $rule;
      rules_item_type_invoke('rules', 'export', array($name, &$export['rules'][$name], &$export, $module));
    }
  }
  if (!empty($module)) {
    // Add the module name as category.
    $rule_set += array('categories' => array());
    $rule_set['categories'][$module] = $module;
    $rule_set['status'] = 'default';
  }
}


/**
 * Prefix exported rules with the module name (if given) and change the status to default.
 * If used through the features integration the module name is given.
 */
function rules_item_rule_export($rule_name, &$rule, &$export, $module = NULL) {
  static $counter = 0;

  // Be sure the exported rule has the right version specified, which is
  // important if the export is used as default.
  $rule['#version'] = 6003;
  
  if (!empty($module)) {
    // Change #status to 'default'.
    $rule['#status'] = 'default';
    // Add the module name as category.
    $rule += array('#categories' => array());
    $rule['#categories'][$module] = $module;
    
    // We increase the counter in any case. As already renamed rules are passed
    // in first, this makes sure to not overwrite the names of those even if
    // they aren't saved yet.
    $counter++;
    
    if (strpos($rule_name, $module) !== 0) {
      // Make sure the name isn't already taken.
      $rules = rules_get_items('rules');
      while (isset($rules[$module .'_'. $counter])) {
        $counter++;
      }

      // Rename the rule.
      $export['rules'][$module .'_'. $counter] = $rule;
      // Unset the non-prefixed item.
      unset($export['rules'][$rule_name]);
    }
  }
}



/**
 * Implementation of hook_features_export_options() for rules.
 */
function rules_features_export_options() {
  // Only export active rules.
  $rules = array_filter(rules_get_configured_items('rules'), '_rules_rule_is_active');
  rules_include('rules_admin');
  // Only export event-triggered rules and omit rules within rule sets.
  $rules = array_filter($rules, 'rules_admin_is_event_rule');
  return rules_admin_get_grouped_labels($rules, '');
}

/**
 * Implementation of hook_features_export_options() for rule sets.
 */
function rules_sets_features_export_options() {
  rules_include('rules_admin');
  return rules_admin_get_grouped_labels(rules_get_configured_items('rule_sets'), '');
}

/**
 * Implementation of hook_features_export_options() for tags.
 */
function rules_categories_features_export_options() {
  return rules_admin_get_categories('rules') + rules_admin_get_categories('rule_sets');
}


/**
 * Implementation of hook_features_export_render() for rules.
 */
function rules_features_export_render($module = 'foo', $data, $features_export) {
  $code = array();
  $code[] = '  $rules = array();';
  $code[] = '';
  
  // Build the usual rules $export structure for rule sets.
  $export = array();
  $items = rules_get_configured_items('rule_sets');
  if (isset($features_export['features']['rules_sets'])) {
    $export['rule_sets'] = array_intersect_key($items, $features_export['features']['rules_sets']);
  }

  $module = strpos($module, '_features_comparison') === 0 ? '' : $module;
  $export = rules_export_items($export, $module);
  $export += array('rules' => array());
  $export['rules'] += $data;
  $defaults = features_var_export($export, '  ');
  
  return array('rules_defaults' => "  return $defaults;");
}

/**
 * Implementation of hook_features_export_render() for sets.
 */
function rules_sets_features_export_render($module = 'foo', $data, $features_export) {
  if (empty($features_export['features']['rules'])) {
    // If there are no rules selected yet, manually
    // invoke the rules export render callback.
    return rules_features_export_render($module, array(), $features_export);
  }
}


/**
 * Implementation of hook_features_export() for rules.
 */
function rules_features_export($data, &$export, $module_name = '') {
  $module = strpos($module_name, '_features_comparison') === 0 ? '' : $module_name;
  
  // First be sure $data contains only rules we have not handled yet.
  $new_data = drupal_map_assoc(array_filter($data, 'is_string'));
  
  // Fire up the rules export already here, so we have the rules
  // renamed *now* so that features knows the right component names.
  $items = rules_get_configured_items('rules');
  // Also pass in the old rules again, so the export knows those rule
  // names are already taken when finding new rule names.
  $rules_export['rules'] = array_filter($data, 'is_array');
  $rules_export['rules'] += array_intersect_key($items, $new_data);

  $rules_export = rules_export_items($rules_export, $module);
  
  // Add to exports
  $export['features']['rules'] = $rules_export['rules'];
  
  // Add rules as a dependency
  $export['dependencies']['rules'] = 'rules';
  
  // Closer look at the rules to be exported...
  $pipe = array();
  rules_include('rules_forms');
  foreach ($export['features']['rules'] as $rule) {
    rules_features_process_rule($rule, $export, $pipe);
  }
  return $pipe;
}

/**
 * Implementation of hook_features_export() for sets.
 */
function rules_sets_features_export($data, &$export, $module_name = '') {
  // Add to exports
  $export['features']['rules_sets'] = drupal_map_assoc($data);
  // Add rules as a dependency
  $export['dependencies']['rules'] = 'rules';
  
  
  // Closer look at the sets to be exported...
  $pipe = array();
  foreach ($export['features']['rules_sets'] as $set_name) {
    rules_features_process_set($set_name, $export, $pipe);
  }
  
  return $pipe;
}

/**
 * Implementation of hook_features_export() for categories.
 */
function rules_categories_features_export($data, &$export, $module_name = '') {
  rules_include('rules_admin');
  module_load_include('inc', 'rules_admin', 'rules_admin.export');

  $rules_export = array();
  rules_admin_export_by_category($rules_export, $data);
  // Pipe through the needed rules and sets.
  $rules_export += array('rules' => array(), 'rule_sets' => array());
  $pipe['rules'] = drupal_map_assoc(array_keys($rules_export['rules']));
  $pipe['rules_sets'] = drupal_map_assoc(array_keys($rules_export['rule_sets']));

  $export['features']['rules_categories'] = drupal_map_assoc($data);
  $export['dependencies']['rules'] = 'rules';

  return $pipe;
}

/**
 * Implementation of hook_features_revert().
 *
 * @param $module
 * name of module to revert content for
 */
function rules_features_revert($module = NULL) {
  // Get default rules items from feature
  if (module_hook($module, 'rules_defaults')) {
    $defaults = module_invoke($module, 'rules_defaults');

    // Delete Normal / Overridden rules items that are defined in code
    foreach ($defaults as $item_type => $items) {
      foreach ($items as $item_name => $item) {
        rules_item_delete($item_type, $item_name);
      }
    }
    rules_clear_cache();
  }
  else {
    drupal_set_message(t('Could not load default rules items.'), 'error');
    return FALSE;
  }
  return TRUE;
}

/**
 * Retrieves all necessary module dependencies for a list of rule sets.
 */
function rules_features_process_set($set_name, &$export, &$pipe) {
  $rules = rules_get_configured_items('rules');
  foreach ($rules as $key => $rule) {
    if ($rule['#set'] == $set_name) {
      rules_features_process_rule($rule, $export, $pipe);
    }
  }
}

/**
 * Processes a rule and identifes needed components or dependencies.
 */
function rules_features_process_rule($rule, &$export, &$pipe) {
  $dependencies = array();
  // If it is an event-triggered rule, add event dependencies
  if (strpos($rule['#set'], 'event_') === 0) {
    $event = substr($rule['#set'], strlen('event_'));
    if ($module = rules_element_providing_module('event', $event)) {
      $export['dependencies'][$module] = $module;
    }
  }
  foreach (array('condition', 'action') as $type) {
    _rules_features_process_rule($rule['#'. $type .'s'], $export, $pipe);
  }
}

/**
 * Helper to recursively process all elements of a rule.
 */
function _rules_features_process_rule($element, &$export, &$pipe) {
  if (isset($element['#type']) && isset($element['#name']) && $module = rules_element_providing_module($element['#type'], $element['#name'])) {
    $export['dependencies'][$module] = $module;
  }
  // Invoke the features_export callback if any.
  $element += array('#settings' => array());
  rules_element_invoke($element, 'features_export', array(&$export, &$pipe, $element['#settings'], $element), FALSE);
  //TODO add input evaluators.
  
  // Recurse
  foreach (element_children($element) as $key) {
    _rules_features_process_rule($element[$key], $export, $pipe);
  }
}


/**
 * Retrieves the providing module for events, conditions and actions.
 *
 * @param $type
 *   'event', 'condition' or 'action'
 * @param $name
 *   the identifier
 * @return
 *   An array of module dependencies.
 */
function rules_element_providing_module($type, $name) {
  static $map = array();

  if (!isset($map[$type])) {
    $map[$type] = array();
    foreach (module_implements('rules_'. $type .'_info') as $module) {
      $module_info = array_keys(module_invoke($module, 'rules_'. $type .'_info'));
      $map[$type] += array_combine($module_info, array_fill(0, count($module_info), $module));
    }
  }
  return isset($map[$type][$name]) ? $map[$type][$name] : FALSE;
}

