<?php
// $Id$

/**
 * @file Includes any rules integration provided by the module.
 */


/**
 * Implements hook_rules_file_info().
 */
function rules_test_rules_file_info() {
  return array('rules_test.test');
}

/**
 * Implements hook_rules_action_info().
 */
function rules_test_rules_action_info() {

  return array(
    'rules_node_publish_action' => array(
      'label' => t('Blabla'),
      'parameter' => array(
        'node' => array('type' => 'node', 'label' => t('Content')),
      ),
      'callbacks' => array(
        'help' => 'rules_test_custom_help',
      ),
      'base' => 'node_publish_action',
    ),
    'rules_node_publish_action_save' => array(
      'label' => t('Blabla'),
      'parameter' => array(
        'node' => array(
          'type' => 'node',
          'label' => t('Content'),
          'save' => TRUE,
        ),
      ),
      'base' => 'node_publish_action',
    ),
    'rules_node_make_sticky_action' => array(
      'label' => t('Blabla'),
      'parameter' => array(
        'node' => array(
          'type' => 'node',
          'label' => t('Content'),
          'save' => TRUE,
        ),
      ),
      'base' => 'node_make_sticky_action',
    ),
    'rules_action_test_reference' => array(
      'label' => t('Blabla'),
      'parameter' => array(
         // For references working right, we need a data type with a wrapper.
        'arg' => array('type' => 'test'),
      ),
    ),
  );
}

/**
 * Implements hook_rules_data_info()
 */
function rules_test_rules_data_info() {
  return array(
    'test' => array(
      'label' => t('content'),
      'class' => 'RulesDataWrapper',
      'module' => 'Node',
      'extenders' => array(
        'RulesDataSavableInterface' => array('class' => 'RulesNodeDataWrapperExtender'),
      ),
      'overrides' => array(
        array(
          'methods' => array('save' => 'rules_test_custom_node_save'),
          'file' => array('module' => 'rules_test', 'name' => 'rules_test.test'),
        ),
      ),
    ),
  );
}

/**
 * Turns the RulesNodeDataWrapper into a extender for testing.
 */
class RulesNodeDataWrapperExtender extends RulesNodeDataWrapper implements FacesExtenderInterface {

  public function __construct(FacesExtendableInterface $object) {
    // Pass through info + date so that the NodeDataWrapper is working right.
    $this->data = &$object->data;
    $this->info = &$object->info;
    parent::setUp();
  }
}

