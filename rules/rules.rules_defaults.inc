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
    'rules_1' =>
    array (
      '#type' => 'rule',
      '#set' => 'event_node_view',
      '#label' => 'Example rule',
      '#active' => 1,
      '#weight' => '0',
      '#name' => 'rules_1',
      '#conditions' =>
      array (
        0 =>
        array (
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
          '#info' =>
          array (
            'label' => 'Content is Page',
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
          '#name' => 'rules_condition_content_is_type',
          '#type' => 'condition',
        ),
      ),
      '#actions' =>
      array (
        0 =>
        array (
          '#type' => 'action',
          '#name' => 'rules_action_node_publish',
          '#info' =>
          array (
            'label' => 'Publish content',
            'label callback' => false,
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
          '#settings' =>
          array (
            '#argument map' =>
            array (
              'node' => 'node',
            ),
            'published' => 1,
          ),
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
