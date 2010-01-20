<?php
// $Id$

/**
 * @file rules integration for the node module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_file_info() on behalf of the node module.
 */
function rules_node_file_info() {
  return array('modules/node.eval');
}

/**
 * Implements hook_rules_event_info() on behalf of the node module.
 */
function rules_node_event_info() {
  $items = array(
    'node_insert' => array(
      'label' => t('After saving new content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('created content')),
      'access callback' => 'entity_metadata_node_access',
    ),
    'node_update' => array(
      'label' => t('After updating existing content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('updated content'), TRUE),
      'access callback' => 'entity_metadata_node_access',
    ),
    'node_presave' => array(
      'label' => t('Content is going to be saved'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('saved content'), TRUE),
      'access callback' => 'entity_metadata_node_access',
    ),
    'node_view' => array(
      'label' => t('Content is going to be viewed'),
      'group' => t('Node'),
      'help' => t("Note that if drupal's page cache is enabled, this event won't be generated for pages served from cache."),
      'variables' => rules_events_node_variables(t('viewed content')) + array(
        'build_mode' => array('type' => 'string', 'label' => t('view mode')),
      ),
      'access callback' => 'entity_metadata_node_access',
    ),
    'node_delete' => array(
      'label' => t('After deleting content'),
      'group' => t('Node'),
      'variables' => rules_events_node_variables(t('deleted content')),
      'access callback' => 'entity_metadata_node_access',
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
 * Implements hook_rules_action_info() on behalf of the node module.
 */
function rules_node_action_info() {
  return array(
    //TODO: replace by data creation action.
    'node_create' => array(
      'label' => t('Create new content'),
      'parameter' => array(
        'author' => array('type' => 'user', 'label' => t('User, who is set as author')),
        'title' => array(
          'type' => 'string',
          'label' => t('Title'),
          'description' => t('The title of the newly created content.'),
        ),
      ),
      'provides' => array(
        'node_added' => array(
          'type' => 'node',
          'label' => t('New content'),
          'save' => TRUE,
          'label callback' => 'rules_action_add_node_variable_label',
        ),
      ),
      'group' => t('Node'),
      'callbacks' => array('execute' => 'rules_action_node_create'),
      'access callback' => 'entity_metadata_node_access',
    ),
    //TODO: replace by action "fetch node" using node argument and direct input form
    // or a general "Fetch data" action.
    'rules_action_load_node' => array(
      'label' => t('Fetch content by id'),
      'parameter' => array(
        'nid' => array('type' => 'integer', 'label' => t('Content ID')),
        'vid' => array(
          'type' => 'integer',
          'label' => t('Content Revision ID'),
          'description' => t("If you want to fetch a specific revision, specify it's revision id. Else leave it empty to fetch the currently active revision."),
          'optional' => TRUE,
        ),
      ),
      'provides' => array(
        'node_loaded' => array(
          'type' => 'node',
          'label' => t('Loaded content'),
          'label callback' => 'rules_action_load_node_variable_label',
        ),
      ),
      'group' => t('Node'),
      'access callback' => 'entity_metadata_node_access',
    ),
    // TODO: Replace by a generic delete data action.
    'rules_action_delete_node' => array(
      'label' => t('Delete content'),
      'parameter' => array(
        'node' => array('type' => 'node', 'label' => t('Content')),
      ),
      'group' => t('Node'),
      'access callback' => 'entity_metadata_node_access',
    ),
  );
}

/**
 * @}
 */
