<?php
// $Id$


/**
 * @file
 * Rules configuration forms for the comment module.
 */

/**
 * Action "Load a comment by id" variable label callback.
 */
function rules_action_load_comment_variable_label($settings) {
  return t('Comment with id @id', array('@id' => $settings['cid']));
}

