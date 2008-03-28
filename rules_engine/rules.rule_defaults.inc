<?php

/**
 * @file Rules default rules
 */
function rules_rules_defaults() {
  $rules = array();
  $rules['config1'] = array(
    '#type' => 'rule',
    '#name' => 'config1',
    '#label' => t('Print the content title of pages and stories'),
    '#set' => 'event_node_view',
    '#module' => 'Workflow-ng-ui',
    '#active' => FALSE,
  );
  //configure a conditoin which evalutates if the node type is a page
  $condition1 = rules_use_condition('rules_condition_token_compare', array(
    '#label' => t('Is page'),
    '#settings' => array(
      'regex' => 0,
      'text1' => '[node:type]',
      'text1_args' => array('node'),
      'text2_args' => array(),
      'text2' => 'page')
    )
  );
  $condition2 = rules_use_condition('rules_condition_token_compare', array(
    '#label' => t('Is story'),
    '#settings' => array(
      'regex' => 0,
      'text1' => '[node:type]',
      'text1_args' => array('node'),
      'text2_args' => array(),
      'text2' => 'story')
    )
  );
  //OR them
  $conditions = rules_configure('OR', $condition1, $condition2);
  //configure an action
  $action = rules_use_action('rules_action_drupal_message', array(
    '#label' => t('Show the title of this content'),
    '#settings' => array(
      'message' => 'Rules example: The title of this post is [node:title]',
      'message_args' => array(),
      'error' => 0,
    )
  ));
  //add the elements to the configuration
  $rules['config1'] = rules_configure($rules['config1'], $conditions, $action);
  
  
  $rules['config2'] = array(
    '#type' => 'rule',
    '#name' => 'config2',
    '#label' => t('Test'),
    '#set' => 'event_node_view',
    '#module' => 'Workflow-ng-ui',
    '#active' => TRUE,
  );
  //configure a conditoin which evalutates if the node type is a page
  $condition1 = rules_use_condition('rules_condition_content_is_type', array(
    '#label' => t('Is page'),
    '#settings' => array(
      'type' => array('page'),
    ),
    '#argument map' => array('node' => 'node'),
  ));
  $condition2 = rules_use_condition('rules_condition_content_is_type', array(
    '#label' => t('Is story'),
    '#settings' => array(
      'type' => array('story'),
    ),
    //'#argument map' => array('node' => 'node'),
  ));
  //OR them
  $conditions = rules_configure('OR', $condition1, $condition2);
  //configure an action

  //add the elements to the configuration
  $rules['config2'] = rules_configure($rules['config2'], $conditions);
  
  return array('rules' => $rules);
}
