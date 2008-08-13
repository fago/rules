<?php
// $Id$
/**
 * @file rules integration for the node module
 */

/**
 * Implementation of hook_event_info()
 *
 * @ingroup rules
 */
function node_rules_event_info() {
  $items = array(
    'node_insert' => array(
      'label' => t('After saving new content'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('created content'), t('content\'s author')),
      'redirect' => TRUE,
    ),
    'node_update' => array(
      'label' => t('After updating existing content'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('updated content'), t('content\'s author'), TRUE),
      'redirect' => TRUE,
    ),
    'node_presave' => array(
      'label' => t('Content is going to be saved'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('saved content'), t('content\'s author'), TRUE),
      'redirect' => TRUE,
    ),
    'node_view' => array(
      'label' => t('Content is going to be viewed'),
      'module' => 'Node',
      'help' => t('Note that if drupal\'s page cache is enabled, this event won\'t be generated for pages served from cache.'),
      'arguments' => rules_events_node_arguments(t('viewed content'), t('content author')) + array(
        'teaser' => array('type' => 'boolean', 'label' => t('Content is displayed as teaser')),
        'page' => array('type' => 'boolean', 'label' => t('Content is displayed as page')),
      ),
    ),
    'node_delete' => array(
      'label' => t('After deleting content'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('deleted content'), t('content author')),
      'redirect' => TRUE,
    ),
  );
  // Specify that on presave the node is saved anyway.
  $items['node_presave']['arguments']['node']['saved'] = TRUE;
  return $items;
}

/**
 * Returns some arguments suitable for using it with a node
 * @ingroup rules
 */
function rules_events_node_arguments($node_label, $author_label, $update = FALSE) {
  $args = array(
    'node' => array(
      'type' => 'node',
      'label' => $node_label,
    ),
    'author' => array(
      'type' => 'user',
      'label' => $author_label,
      'handler' => 'rules_events_argument_node_author'),
    );
  if ($update) {
    $args += array(
      'node_unchanged' => array(
        'type' => 'node',
        'label' => t('unchanged content'),
        'handler' => 'rules_events_argument_node_unchanged',
      ),
      'author_unchanged' => array(
        'type' => 'user',
        'label' => t('unchanged content\'s author'),
        'handler' => 'rules_events_argument_unchanged_node_author',
      ),
    );
  }
  return $args + rules_events_global_user_argument();
}

/**
 * Gets the author's account of a node
 * @ingroup rules
 */
function rules_events_argument_node_author($node) {
  return user_load(array('uid' => $node->uid));
}

/**
 * Gets the node object, that doesn't contain the modified changes
 * @ingroup rules
 */
function rules_events_argument_node_unchanged($node) {
  return $node->nid ? node_load($node->nid) : $node;
}

/**
 * Gets the author of the unchanged node object
 * @ingroup rules
 */
function rules_events_argument_unchanged_node_author($node) {
  return rules_events_argument_node_author(rules_events_argument_node_unchanged($node));
}



/**
 * Implementation of hook_rules_condition_info()
 * @ingroup rules
 */
function node_rules_condition_info() {
  $items = array();
  $defaults = array(
    'arguments' => array(
      'node' => array('type' => 'node', 'label' => t('Content')),
    ),
    'module' => 'Node',
  );
  $items['rules_condition_content_is_type'] = $defaults + array(
    'label' => t('Content has type'),
    'help' => t('Evaluates to TRUE, if the given content has one of the selected content types.'),
  );
  $items['rules_condition_content_is_published'] = $defaults + array(
    'label' => t('Content is published'),
  );
  $items['rules_condition_content_is_sticky'] = $defaults + array(
    'label' => t('Content is sticky'),
  );
  $items['rules_condition_content_is_promoted'] = $defaults + array(
    'label' => t('Content is promoted to frontpage'),
  );
  return $items;
}

/**
 * Condition: Check for selected content types
 */
function rules_condition_content_is_type(&$node, $settings) {
  return in_array($node->type, $settings['type']);
}

/**
 * Condition: Check if the node is published
 */
function rules_condition_content_is_published(&$node, $settings) {
  return $node->status == 1;
}

/**
 * Condition: Check if the node is sticky
 */
function rules_condition_content_is_sticky(&$node, $settings) {
  return $node->sticky == 1;
}

/**
 * Condition: Check if the node is promoted to the frontpage
 */
function rules_condition_content_is_promoted(&$node, $settings) {
  return $node->promote == 1;
}


/**
 * Implementation of hook_rules_action_info
 * @ingroup rules
 */
function node_rules_action_info() {
  return array(
    'rules_action_node_set_author' => array(
      'label' => t('Set the content author'),
      'arguments' => array(
        'node' => array('type' => 'node', 'label' => t('Content')),
        'author' => array('type' => 'user', 'label' => t('User, which is set as author')),
      ),
      'module' => 'Node',
    ),
    'rules_action_add_node' => array(
      'label' => t('Add new content'),
      'arguments' => array(
        'author' => array('type' => 'user', 'label' => t('User, which is set as author')),
        'title' => array(
          'type' => 'string',
          'label' => t('Title'),
          'description' => t('The title of the newly created content.'),
        ),
      ),
      'new variables' => array(
        'node_added' => array(
          'type' => 'node',
          'label' => t('New content'),
          'save' => TRUE,
          'label callback' => 'rules_action_add_node_variable_label',
        ),
      ),
      'module' => 'Node',
    ),
    'rules_action_load_node' => array(
      'label' => t('Load content by id'),
      'arguments' => array(
        'nid' => array('type' => 'number', 'label' => t('Content ID')),
        'vid' => array(
          'type' => 'number',
          'label' => t('Content Revision ID'),
          'description' => t('If you want to load a specific revision, specify it\'s revision id. Else leave it empty to load the current revision.'),
          'required' => FALSE,
        ),
      ),
      'new variables' => array(
        'node_loaded' => array(
          'type' => 'node',
          'label' => t('Loaded content'),
          'label callback' => 'rules_action_load_node_variable_label',
        ),
      ),
      'module' => 'Node',
    ),
  );
}


/**
 * Modifies a node as configured
 * @ingroup rules
 */
function rules_action_node_set_author($node, $author) {
  $node->uid = $author->uid;
  $node->name = $author->name;
  return array('node' => $node);
}

/**
 * Action "Add a node"
 * @ingroup rules
 */
function rules_action_add_node($author, $title, $settings) {
  if (!$settings['node_access'] || node_access('create', $settings['type'], $author)) {
    module_load_include('inc', 'node', 'node.pages');

    $node = (object)array('type' => $settings['type']);
    node_object_prepare($node);

    $node->name = $author->name;
    $node->uid = $author->uid;
    $node->type = $settings['type'];
    $node->title = $title;
    return array('node_added' => $node);
  }
}

/**
 * Loads a node
 */
function rules_action_load_node($nid, $vid = NULL) {
  return array('node_loaded' => node_load($nid, $vid ? $vid : NULL));
}





/**
 * Implementation of hook_rules_action_info_alter().
 *
 * Adapts the action info of the core actions to better fit for rules.
 */
function node_rules_action_info_alter(&$actions) {
  // The rules action is more powerful, so hide the core action
  unset($actions['rules_core_node_assign_owner_action']);
  // We prefer handling saving by rules - not by the user.
  unset($actions['rules_core_node_save_action']);
}


/**
 * Implementation of hook_rules_data_type_info()
 */
function node_rules_data_type_info() {
  return array(
    'node' => array(
      'label' => t('Content'),
      'class' => 'rules_data_type_node',
      'savable' => TRUE,
      'identifiable' => TRUE,
      'module' => 'Node',
    ),
  );
}

/**
 * Defines the node type
 * @ingroup rules
 */
class rules_data_type_node extends rules_data_type {

  function save() {
    $node = &$this->get();
    node_save($node);
    return TRUE;
  }

  function load($nid) {
    return node_load($nid);
  }

  function get_identifier() {
    $node = &$this->get();
    return $node->nid;
  }
}

