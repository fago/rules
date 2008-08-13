<?php
// $Id$
/**
 * @file Rules default rules
 */


function rules_rules_defaults() {
  $config =
array (
  'rules' =>
  array (
    'rules_default_rule_1' =>
    array (
      '#type' => 'rule',
      '#set' => 'event_node_view',
      '#label' => 'Example rule: When viewing an unpublished page, publish it.',
      '#active' => 0,
      '#weight' => '0',
      '#name' => 'rules_default_rule_1',
      '#conditions' =>
      array (
        1 =>
        array (
          '#settings' =>
          array (
            '#argument map' =>
            array (
              'node' => 'node',
            ),
          ),
          '#negate' => 1,
          '#info' =>
          array (
            'label' => 'viewed content is published',
            'arguments' =>
            array (
              'node' =>
              array (
                'type' => 'node',
                'label' => 'Content',
              ),
            ),
            'module' => 'Node',
          ),
          '#name' => 'rules_condition_content_is_published',
          '#type' => 'condition',
        ),
        0 =>
        array (
          '#type' => 'condition',
          '#name' => 'rules_condition_content_is_type',
          '#info' =>
          array (
            'label' => 'viewed content is Page',
            'arguments' =>
            array (
              'node' =>
              array (
                'type' => 'node',
                'label' => 'Content',
              ),
            ),
            'help' => 'Evaluates to TRUE, if the given content has one of the selected content types.',
            'module' => 'Node',
          ),
          '#settings' =>
          array (
            '#argument map' =>
            array (
              'node' => 'node',
            ),
            'type' =>
            array (
              'page' => 'page',
            ),
          ),
        ),
      ),
      '#actions' =>
      array (
        0 =>
        array (
          '#settings' =>
          array (
            '#argument map' =>
            array (
              'node' => 'node',
            ),
            'auto_save' => 1,
          ),
          '#info' =>
          array (
            'label' => 'Publish viewed viewed content',
            'module' => 'Node',
            'arguments' =>
            array (
              'node' =>
              array (
                'label' => 'Content',
                'type' => 'node',
              ),
            ),
            'base' => 'rules_core_action_execute',
            'action_name' => 'node_publish_action',
            'configurable' => false,
            'label callback' => 'rules_core_node_label_callback',
          ),
          '#name' => 'rules_core_node_publish_action',
          '#type' => 'action',
        ),
      ),
    ),
  ),
);

  return $config + array(
    'rule_sets' => array(
      'rules_set_1' => array(
        'label' => 'Rule Set Example working with content',
        'arguments' => array('node' => array('type' => 'node', 'label' => 'Content')),
       ),
     ),
   );
}
