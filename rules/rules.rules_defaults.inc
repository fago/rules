<?php
// $Id$
/**
 * @file Rules default rules
 */


function rules_rules_defaults() {
  $rules = array();

  return array('rules' => $rules,
    'rule_sets' => array(
      'rules_set_1' => array(
        'label' => 'Rule Set Example working with content',
        'arguments' => array('node' => array('type' => 'node', 'label' => 'Content')),
       ),
     ),
   );
}
