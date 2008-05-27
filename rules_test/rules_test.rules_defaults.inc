<?php
// $Id$
/**
 * @file Rules default rules - this rules are used by the tests
 */


function rules_test_rules_defaults() {
  $rules = array();

  
  $rules['rules_test_1'] = array(
    '#type' => 'rule',
    '#name' => 'rules_test_1',
    '#label' => t('Test altering arguments by reference'),
    '#set' => 'rules_test_1',
    '#active' => TRUE,
  );
  $condition1 = rules_use_condition('rules_condition_alter_arg', array(
    '#argument map' => array('rules_test' => 'rules_test'),
    '#weight' => -1,
  ));
  $condition2 = rules_use_condition('rules_condition_check_for_altered_arg', array(
    '#argument map' => array('rules_test' => 'rules_test'),
  ));
  $action = rules_use_action('rules_log_msg', array(
    '#settings' => array(
      'msg' => 'Rules Test 1 - Success!',
    )
  ));
  $rules['rules_test_1'] += array(
    '#conditions' => array($condition1, $condition2),
    '#actions' => array($action),
  );
  
  $rules['rules_test_2_1'] = array(
    '#type' => 'rule',
    '#name' => 'rules_test_2_1',
    '#label' => t('Test changing arguments per action'),
    '#set' => 'rules_test_2',
    '#active' => TRUE,
    '#weight' => -1,
  );
  $action = rules_use_action('rules_change_arg', array(
    '#argument map' => array('rules_test' => 'rules_test'),
  ));
  $rules['rules_test_2_1'] += array(
    '#conditions' => array(),
    '#actions' => array($action),
  );

  $rules['rules_test_2_2'] = array(
    '#type' => 'rule',
    '#name' => 'rules_test_2_2',
    '#label' => t('Test changing arguments per action - check'),
    '#set' => 'rules_test_2',
    '#active' => TRUE,
  );
  $condition = rules_use_condition('rules_condition_check_for_altered_arg', array(
    '#argument map' => array('rules_test' => 'rules_test'),
  ));
  $action = rules_use_action('rules_log_msg', array(
    '#settings' => array(
      'msg' => 'Rules Test 2 - Success!',
    )
  ));
  $rules['rules_test_2_2'] += array(
    '#conditions' => array($condition),
    '#actions' => array($action),
  );
  
  
  $rules['rules_test_4_1'] = array(
    '#type' => 'rule',
    '#name' => 'rules_test_4_1',
    '#label' => t('Test changing arguments per action'),
    '#set' => 'rules_test_4',
    '#active' => TRUE,
  );
  $condition = rules_use_condition('rules_condition_check_for_altered_arg', array(
    '#argument map' => array('rules_test_2' => 'rules_test'),
  ));
  $action = rules_use_action('rules_log_msg', array(
    '#settings' => array(
      'msg' => 'Rules Test 4 - Success!',
    )
  ));
  $rules['rules_test_4_1'] += array(
    '#conditions' => array($condition),
    '#actions' => array($action),
  );

  $rules['rules_test_5'] = array(
    '#type' => 'rule',
    '#name' => 'rules_test_5',
    '#label' => t('Print the content title of pages and stories'),
    '#set' => 'event_node_view',
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
  $action = rules_use_action('rules_action_node_publish', array(
    '#settings' => array(
      'published' => FALSE,
    ),
    '#argument map' => array('node' => 'node'),
  ));
  $rules['rules_test_5'] += array(
    '#conditions' => array($condition1),
    '#actions' => array($action),
  );

  return array(
    'rules' => $rules,
    'rule_sets' => array(
      'rules_test_1' => array(
        'label' => 'Rule Testing 1',
        'arguments' => array('rules_test' => array('entity' => 'rules_test', 'label' => 'node')),
       ),
      'rules_test_2' => array(
        'label' => 'Rule Testing 2',
        'arguments' => array('rules_test' => array('entity' => 'rules_test', 'label' => 'node')),
       ),
      'rules_test_4' => array(
        'label' => 'Rule Testing 4',
        'arguments' => array(
          'rules_test_1' => array('entity' => 'rules_test', 'label' => 'node'),
          'rules_test_2' => array('entity' => 'rules_test', 'label' => 'node', 'handler' => 'rules_test_4_load_entity'),
         ),
       ),
    ),
  );
}


