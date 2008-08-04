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
  return array(
    'node_insert' => array(
      'label' => t('Content has been created'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('created content'), t('content\'s author')),
      'redirect' => TRUE,
    ),
    'node_update' => array(
      'label' => t('Content has been updated'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('updated content'), t('content author')) + array(
        'node_unchanged' => array('type' => 'node', 'label' => t('unchanged content'), 'handler' => 'rules_events_argument_node_unchanged'),
        'author_unchanged' => array('type' => 'user', 'label' => t('unchanged content\'s author'), 'handler' => 'rules_events_argument_unchanged_node_author'),
      ),
      'redirect' => TRUE,
    ),
    'node_submit' => array(
      'label' => t('Content is going to be saved'),
      'module' => 'Node',
      'arguments' => array(
        'node' => array('type' => 'node', 'label' => t('content'), 'saved' => TRUE),
        'author' => array('type' => 'user', 'label' => t('content author'), 'handler' => 'rules_events_argument_node_author'),
        'node_unchanged' => array('type' => 'node', 'label' => t('unchanged content'), 'handler' => 'rules_events_argument_node_unchanged'),
        'author_unchanged' => array('type' => 'user', 'label' => t('unchanged content\'s author'), 'handler' => 'rules_events_argument_unchanged_node_author'),
      ) + rules_events_global_user_argument(),
      'redirect' => TRUE,
    ),
    'node_view' => array(
      'label' => t('Content is going to be viewed'),
      'module' => 'Node',
      'description' => t('Note that if drupal\'s page cache is enabled, this event won\'t be generated for pages served from cache.'),
      'arguments' => rules_events_node_arguments(t('viewed content'), t('content author')) + array(
        'teaser' => array('type' => 'boolean', 'label' => t('Content is displayed as teaser')),
        'page' => array('type' => 'boolean', 'label' => t('Content is displayed as page')),
      ),
    ),
    'node_delete' => array(
      'label' => t('Content has been deleted'),
      'module' => 'Node',
      'arguments' => rules_events_node_arguments(t('deleted content'), t('content author')),
      'redirect' => TRUE,
    ),
  );
}

/**
 * Returns some arguments suitable for using it with a node
 * @ingroup rules
 */
function rules_events_node_arguments($node_label, $author_label) {
  return array(
    'node' => array('type' => 'node', 'label' => $node_label),
    'author' => array('type' => 'user', 'label' => $author_label, 'handler' => 'rules_events_argument_node_author'),
  ) + rules_events_global_user_argument();
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
  return array(
    'rules_condition_content_is_type' => array(
      'label' => t('Content has type'),
      'arguments' => array(
        'node' => array('type' => 'node', 'label' => t('Content')),
      ),
      'help' => t('Evaluates to TRUE, if the given content has one of the selected content types.'),
      'module' => 'Node',
    ),
  );
}

/**
 * Condition: Check for selected content types
 * @ingroup rules
 */
function rules_condition_content_is_type(&$node, $settings) {
  return in_array($node->type, $settings['type']);
}

/**
 * Condition: Check for selected content types - Label callback
 * @ingroup rules
 */
function rules_condition_content_is_type_label($settings) {
  $names = array_intersect_key(node_get_types('names'), $settings['type']);
  return t('Content is @type', array('@type' => implode(' or ', $names)));
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
        'author' => array('type' => 'user', 'label' => t('User, which is set as author of the content')),
     // 'title' => array('type' => 'string', 'label' => t('Title'), 'description' => t('Enter the title of the newly created content.')),
      ),
      'new variables' => array(
        'node_added' => array('type' => 'node', 'label' => t('Added content'), 'save' => TRUE),
      ),
      'module' => 'Node',
    ),
    'rules_action_load_node' => array(
      'label' => t('Load content by id'),
      'new variables' => array(
        'node_loaded' => array('type' => 'node', 'label' => t('Loaded content'), 'label callback' => 'rules_action_load_node_variable_label'),
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

function rules_action_node_set_author_label($settings, $argument_labels) {
  return t('Set the author of @node to @author', $argument_labels);
}

/**
 * Action "Add a node"
 * @ingroup rules
 */
function rules_action_add_node($author, $settings) {
  if (!$settings['node_access'] || node_access('create', $settings['type'], $author)) {
    module_load_include('inc', 'node', 'node.pages');

    $node = (object)array('type' => $settings['type']);
    node_object_prepare($node);

    $node->name = $author->name;
    $node->uid = $author->uid;
    $node->type = $settings['type'];
    $node->title = $settings['title'];
    return array('node_added' => $node);
  }
}

/**
 * Loads a node
 * @ingroup rules
 */
function rules_action_load_node($settings) {
//TODO: fix token
  return array('node_loaded' => node_load($settings['nid'], $settings['vid'] ? $settings['vid'] : NULL));
}

function rules_action_load_node_variable_label($settings) {
  return t('Content with id @id', array('@id' => $settings['nid']));
}

/**
 * Implementation of hook_rules_data_type_info()
 */
function node_rules_data_type_info() {
  return array(
    'node' => array(
      'label' => t('Content'),
      'class' => 'rules_data_type_node',
      'saveable' => TRUE,
      'identifiable' => TRUE,
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

