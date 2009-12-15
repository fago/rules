<?php
// $Id$

/**
 * @file Includes any rules integration provided by the module.
 */

/**
 * Load all module includes as soon as this file gets included, which is done
 * automatically by module_implements().
 */
foreach (rules_core_modules() as $module) {
  module_load_include('inc', 'rules', "modules/$module.rules");
}

/**
 * Defines a list of core module on whose behalf we provide module integration.
 */
function rules_core_modules() {
  return array('node', 'system');
}

/**
 * Implements hook_rules_file_info().
 */
function rules_rules_file_info() {
  $items = array();
  foreach (rules_core_modules() as $module) {
    if (function_exists($function = "rules_{$module}_file_info")) {
      $items = array_merge($items, $function());
      // Automatically add "$module.rules_forms.inc" for each module.
      $items[] = $module . '.rules_forms';
    }
  }
  return $items;
}

/**
 * Implements hook_rules_action_info().
 */
function rules_rules_action_info() {
  $items = array();
  foreach (rules_core_modules() as $module) {
    if (function_exists($function = "rules_{$module}_action_info")) {
      $items += $function();
    }
  }
  return $items;
}

/**
 * Implements hook_rules_condition_info().
 */
function rules_rules_condition_info() {
  $items = array();
  foreach (rules_core_modules() as $module) {
    if (function_exists($function = "rules_{$module}_condition_info")) {
      $items += $function();
    }
  }
  return $items;
}

/**
 * Implements hook_rules_event_info().
 */
function rules_rules_event_info() {
  $items = array();
  foreach (rules_core_modules() as $module) {
    if (function_exists($function = "rules_{$module}_event_info")) {
      $items += $function();
    }
  }
  return $items;
}

/**
 * Implements hook_rules_data_info().
 */
function rules_rules_data_info() {
  $items = array();
  foreach (rules_core_modules() as $module) {
    if (function_exists($function = "rules_{$module}_data_info")) {
      $items += $function();
    }
  }
  return $items;
}

/**
 * Returns the parameter definition for the acting user (global $user)
 */
function rules_events_global_user_parameter() {
  return array('user' => array('type' => 'user', 'label' => t('acting user'), 'handler' => 'rules_events_parameter_global_user'));
}
