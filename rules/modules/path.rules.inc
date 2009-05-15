<?php
// $Id$


/**
 * @file Rules integration for the path module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implementation of hook_rules_condition_info().
 */
function path_rules_condition_info() {
  return array(
    'rules_condition_url_has_alias' => array(
      'label' => t('Path has alias(es)'),
      'module' => 'Path',
      'eval input' => array('src'),
    ),
    'rules_condition_alias_exists' => array(
      'label' => t('URL alias exists'),
      'module' => 'Path',
      'eval input' => array('dst'),
    ),
  );
}

/**
 * Condition implementation: Check if the path has an alias
 */
function rules_condition_url_has_alias($settings) {
  return (bool)drupal_lookup_path('alias', $settings['src']);
}

/**
 * Condition implementation: Check if the URL alias already exists
 */
function rules_condition_alias_exists($settings) {
  $dst = rules_clean_path($settings['dst'], $settings['replace']);
  return (bool)drupal_lookup_path('source', $dst);
}

/**
 * Implementation of hook_rules_action_info().
 */
function path_rules_action_info() {
  return array(
    'rules_action_path_alias' => array(
      'label' => t('Create or delete an URL alias'),
      'module' => 'Path',
      'eval input' => array('src', 'dst'),
    ),
    'rules_action_node_path_alias' => array(
      'label' => t("Create or delete a content's URL alias"),
      'arguments' => array(
        'node' => array('type' => 'node', 'label' => t('Content')),
      ),
      'module' => 'Path',
      'eval input' => array('dst'),
    ),
  );
}

/**
 * Action Implementation
 */
function rules_action_path_alias($settings) {
  $dst = rules_clean_path($settings['dst'], $settings['replace']);
  path_set_alias($settings['src'], $dst);
}

/**
 * Action Implementation
 */
function rules_action_node_path_alias($node, $settings) {
  $node->path = rules_clean_path($settings['dst'], $settings['replace']);
  return array('node' => $node);
}

/**
 * Cleans the given path by replacing non ASCII characters with the given
 * $replace parameter
 *
 * Furthermore it allows customization by overriding the variable rules_clean_path in
 * the settings.php
 */
function rules_clean_path($path, $replace) {
  if ($replace) {
    $array = variable_get('rules_clean_path', array('/[^a-zA-Z0-9\/\-_]+/', $replace));
    $array[2] = $path;
    //replace it and remove trailing and leading replacement characters
    return trim(call_user_func_array('preg_replace', $array), $replace);
  }
  else {
    return $path;
  }
}

/**
 * @}
 */
