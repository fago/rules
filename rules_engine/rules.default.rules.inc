<?php

/**
 * @file Workflow-ng default configurations
 * 
 * Workflow-ng does already use hook_configuration itself, so we can't implement
 * hook_configuration again. Instead this gets called from the implementation and added
 * to the configurations. 
 */

function workflow_ng_default_configuration() {
  $configurations = array();
  $configurations['config1'] = array(
    '#label' => t('Print the content title of pages and stories'),
    '#event' => 'node_view',
    '#module' => 'Workflow-ng-ui',
    '#active' => FALSE,
  );
  //configure a conditoin which evalutates if the node type is a page
  $condition1 = workflow_ng_use_condition('workflow_ng_condition_token_compare', array(
    '#label' => t('Is page'),
    '#settings' => array(
      'regex' => 0,
      'text1' => '[node:type]',
      'text1_args' => array('node'),
      'text2_args' => array(),
      'text2' => 'page')
    )
  );
  $condition2 = workflow_ng_use_condition('workflow_ng_condition_token_compare', array(
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
  $conditions = workflow_ng_configure('OR', $condition1, $condition2);
  //configure an action
  $action = workflow_ng_use_action('workflow_ng_action_drupal_message', array(
    '#label' => t('Show the title of this content'),
    '#settings' => array(
      'message' => 'Workflow-ng example: The title of this post is [node:title]',
      'message_args' => array(),
      'error' => 0,
    )
  ));
  //add the elements to the configuration
  $configurations['config1'] = workflow_ng_configure($configurations['config1'], $conditions, $action);
  return $configurations;
}
