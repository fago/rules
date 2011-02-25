<?php


/**
 * @file
 * Rules configuration forms for the comment module.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Action "Load a comment by id" variable label callback.
 */
function rules_action_load_comment_variable_label($settings) {
  return t('Comment with id @id', array('@id' => $settings['cid']));
}

/**
 * @}
 */