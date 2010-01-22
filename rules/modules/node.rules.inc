<?php
// $Id$

/**
 * @file rules integration for the node module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_event_info() on behalf of the node module.
 */
function rules_node_event_info() {
  $items = array(
    'node_insert' => array(
      'label' => t('After saving new content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('created content')),
      'access callback' => 'rules_node_integration_access',
    ),
    'node_update' => array(
      'label' => t('After updating existing content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('updated content'), TRUE),
      'access callback' => 'rules_node_integration_access',
    ),
    'node_presave' => array(
      'label' => t('Before saving content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('saved content'), TRUE),
      'access callback' => 'rules_node_integration_access',
    ),
    'node_view' => array(
      'label' => t('Content is viewed'),
      'group' => t('Node'),
      'help' => t("Note that if drupal's page cache is enabled, this event won't be generated for pages served from cache."),
      'variables' => rules_events_node_variables(t('viewed content')) + array(
        'view_mode' => array(
          'type' => 'text',
          'label' => t('view mode'),
          'options list' => 'rules_get_entity_view_modes',
        ),
      ),
      'access callback' => 'rules_node_integration_access',
    ),
    'node_delete' => array(
      'label' => t('After deleting content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('deleted content')),
      'access callback' => 'rules_node_integration_access',
    ),
  );
  // Specify that on presave the node is saved anyway.
  $items['node_presave']['variables']['node']['skip save'] = TRUE;
  return $items;
}

/**
 * Returns some parameter suitable for using it with a node
 */
function rules_events_node_variables($node_label, $update = FALSE) {
  $args = array(
    'node' => array('type' => 'node', 'label' => $node_label),
  );
  if ($update) {
    $args += array(
      'node_unchanged' => array(
        'type' => 'node',
        'label' => t('unchanged content'),
        'handler' => 'rules_events_node_var_unchanged',
      ),
    );
  }
  return $args;
}

/**
 * Node integration access callback.
 */
function rules_node_integration_access($type, $name) {
  if ($type == 'event' || $type == 'condition') {
    return entity_metadata_node_access('view', 'node');
  }
}

/**
 * @}
 */
