<?php
// $Id$
/**
 * @file rules integration for the node module 
 */

/** 
 * Implementation of hook_event_info()
 */
function node_rules_event_info() {
  return array(
    'node_insert' => array(
      'label' => t('A post has been created'),
      'module' => 'node',
      'arguments' => rules_events_node_arguments(t('created content'), t('content\'s author')),
      'redirect' => TRUE,
    ),
    'node_update' => array(
      'label' => t('A post has been updated'),
      'module' => 'node',
      'arguments' => rules_events_node_arguments(t('updated content'), t('content author')) + array(
        'node_unchanged' => array('entity' => 'node', 'label' => t('unchanged content'), 'handler' => 'rules_events_argument_node_unchanged'),
        'author_unchanged' => array('entity' => 'user', 'label' => t('unchanged content\'s author'), 'handler' => 'rules_events_argument_unchanged_node_author'),
      ),
      'redirect' => TRUE,
    ),
    'node_submit' => array(
      'label' => t('A post is going to be saved'),
      'module' => 'Node',
      'arguments' => array(
        'node' => array('entity' => 'node', 'label' => t('content'), 'saved' => TRUE),
        'author' => array('entity' => 'user', 'label' => t('content author'), 'handler' => 'rules_events_argument_node_author'),
        'node_unchanged' => array('entity' => 'node', 'label' => t('unchanged content'), 'handler' => 'rules_events_argument_node_unchanged'),
        'author_unchanged' => array('entity' => 'user', 'label' => t('unchanged content\'s author'), 'handler' => 'rules_events_argument_unchanged_node_author'),
      ) + rules_events_global_user_argument(),
      'redirect' => TRUE,
    ),
    'node_view' => array(
      'label' => t('Content is going to be viewed'),
      'module' => 'Node',
      'description' => t('Note that if drupal\'s page cache is enabled, this event won\'t be generated for pages served from cache.'),
      'arguments' => array(
        'teaser' => array('entity' => 'boolean', 'label' => t('Content is displayed as teaser')),
        'page' => array('entity' => 'boolean', 'label' => t('Content is displayed as page')),
      ) + rules_events_node_arguments(t('viewed content'), t('content author')),
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
 */
function rules_events_node_arguments($node_label, $author_label) {
  return array(
    'node' => array('entity' => 'node', 'label' => $node_label),
    'author' => array('entity' => 'user', 'label' => $author_label, 'handler' => 'rules_events_argument_node_author'),
  ) + rules_events_global_user_argument();
}

/**
 * Implementation of hook_nodeapi
 */
function rules_nodeapi(&$node, $op, $teaser = NULL, $page = NULL) {

  if (in_array($op, array('view', 'insert', 'update', 'submit', 'delete'))) {
    //we pass the node by reference so that changes are saved automatically on op submit
    $arguments = ($op == 'view') ? array('node' => &$node, 'teaser' => $teaser, 'page' => $page) : array('node' => &$node);
    rules_invoke_event('node_'. $op, $arguments);
  }
}

/**
 * Gets the author's account of a node
 */
function rules_events_argument_node_author($node) {
  return user_load(array('uid' => $node->uid));
}

/**
 * Gets the node object, that doesn't contain the modified changes
 */
function rules_events_argument_node_unchanged($node) {
  return $node->nid ? node_load($node->nid) : $node;
}

/**
 * Gets the author of the unchanged node object
 */
function rules_events_argument_unchanged_node_author($node) {
  return rules_events_argument_node_author(rules_events_argument_node_unchanged($node));
}





/**
 * Implementation of hook_condition_info()
 */
function node_rules_condition_info() {
  return array(
    'rules_condition_content_is_type' => array(
      'label' => t('Content has type'),
      'arguments' => array(
        'node' => array('entity' => 'node', 'label' => t('Content')),
      ),
      'description' => t('Evaluates to TRUE, if the given content has one of the selected content types.'),
      'module' => 'Node',
    ),
  );
}

/**
 * Condition: Check for selected content types
 */
function rules_condition_content_is_type($node, $settings) {
  return in_array($node->type, $settings['type']);
}

/**
 * Implementation of hook_rules_action_info
 */
function node_rules_action_info() {
  return array(
    'rules_action_node_set_author' => array(
      'label' => t('Set the post author'),
      'arguments' => array(
        'node' => array('entity' => 'node', 'label' => t('Content')),
        'author' => array('entity' => 'user', 'label' => t('User, which is set as author')),
      ),
      'module' => 'Node',
    ),
    'rules_action_node_publish' => array(
      'label' => t('Publish/unpublish content'),
      'arguments' => array(
        'node' => array('entity' => 'node', 'label' => t('Content')),
      ),
      'module' => 'Node',
    ),
    'rules_action_node_promote' => array(
      'label' => t('Promote/demote content to/from the front page'),
      'arguments' => array(
        'node' => array('entity' => 'node', 'label' => t('Content')),
      ),
      'module' => 'Node',
    ),
    'rules_action_add_node' => array(
     'label' => t('Add new content'),
     'arguments' => array(
       'author' => array('entity' => 'user', 'label' => t('User, which is set as author of the content')),
      ),
     'new arguments' => array(
       'node_added' => array('entity' => 'node', 'label' => t('Added content')),
      ),
      'module' => 'Node',
    ),
    'rules_action_load_node' => array(
     'label' => t('Load content by id'),
     'new arguments' => array(
       'node_loaded' => array('entity' => 'node', 'label' => t('Loaded content')),
      ),
      'module' => 'Node',
    ),     
  );
}


/**
 * Modifies a node as configured
 */
function rules_action_node_set_author($node, $author) {
  $node->uid = $author->uid;
  $node->name = $author->name;
  return array('node' => $node);
}

/**
 * Sets a node's Published flag
 */
function rules_action_node_publish($node, $settings, &$arguments, &$log) {
  if ($node->status != $settings['published']) {
    $node->status = $settings['published'];
    return array('node' => $node);
  }
}

/**
 * Sets a node's Promoted flag
 */
function rules_action_node_promote($node, $settings, &$arguments, &$log) {
  if ($node->promote != $settings['promote']) {
    $node->promote = $settings['promote'];
    
  }
}

/**
 * Action "Add a node"
 */
function rules_action_add_node($author, $settings, &$arguments, &$log) {
  //check if WF-ng should check for access rights of the acting user on node creation.
  //TODO: In Drupal6, node_access($op, $node, $account = NULL), thus will allow checking also content author
  if (!$settings['node_access'] || node_access('create', $settings['type'])) {
    $node = (object)array();
  	node_object_prepare($node);
  	//load node options
  	$node_options = variable_get('node_options_'. $settings['type'], array('status', 'promote'));
  	foreach (array('status', 'moderate', 'promote', 'sticky', 'revision') as $key) {
  	  $node->$key = in_array($key, $node_options);
  	}

  	$node->name = $author->name;
  	$node->uid = $author->uid;
  	$node->type = $settings['type'];
  	$node->title = rules_token_replace($settings['title'], $settings['title_args'], $arguments, $log);
    return array('new arguments' => array('node_added' => $node), 'node_added' => $node);
  }
}

/**
 * Loads a node
 */
function rules_action_load_node($settings, &$arguments, &$log) {
  extract( rules_token_replace_all(array('nid', 'vid'), $settings, $arguments, $log) );

  return array('new arguments' => array('node_loaded' => node_load($nid, $vid ? $vid : NULL)));
}
