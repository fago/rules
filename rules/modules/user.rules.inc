<?php
// $Id$

/**
 * @file rules integration for the user module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_file_info() on behalf of the user module.
 */
function rules_user_file_info() {
  //return array('modules/user.eval');
}

/**
 * Implements hook_rules_data_info() on behalf of the user module.
 */
function rules_user_data_info() {
  return array(
    'user' => array(
      'label' => t('content'),
      'class' => 'RulesUserDataWrapper',
      'parent' => 'entity',
    ),
  );
}

/**
 * @}
 */
