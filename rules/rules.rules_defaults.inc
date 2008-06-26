<?php
// $Id$
/**
 * @file Rules default rules
 */


function rules_rules_defaults() {
  $rules = array();
  $rules['rules1'] = array(
    '#type' => 'rule',
    '#name' => 'rules1',
    '#label' => t('Print the content title of pages and stories'),
    '#set' => 'event_node_view',
    '#module' => 'rules',
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
  $OR = rules_configure('OR', $condition1, $condition2);
  //configure an action
  $action = rules_use_action('rules_action_drupal_message', array(
    '#label' => t('Show the title of this content'),
    '#settings' => array(
      'message' => 'Rules example: The title of this post is [node:title]',
      'message_args' => array(),
      'error' => 0,
    )
  ));
  //add the elements to the rule
  $rules['rules1'] += array(
    '#conditions' => array($OR),
    '#actions' => array($action),
  );
  return array('rules' => $rules);
}
