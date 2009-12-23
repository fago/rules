<?php
// $Id$

/**
 * @file General data related rules integration
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_file_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_file_info() {
  return array('modules/data.eval');
}

/**
 * Implements hook_rules_data_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_data_info() {
  return array(

  );
}

/**
 * Implements hook_rules_action_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_action_info() {
  return array(
    'rules_action_set' => array(
      'label' => t('Modify data'),
      'parameter' => array(
        'data' => array(
          'type' => '*',
          'label' => t('Data to modify'),
          'description' => t('Specify the data to be modified using a data selector, e.g. "node:author:name".'),
          'save' => TRUE,
         ),
        'value' => array(
          'type' => '*',
          'label' => t('Data value'),
          'description' => t('The new value to set for the selected data.'),
        ),
      ),
      'group' => t('Data'),
    ),
  );
}



/**
 * @}
 */
