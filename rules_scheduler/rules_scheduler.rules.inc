<?php
// $Id$
/**
 * @file rules integration for the rules scheduler module
 */

/**
 * Implementation of hook_rules_action_info().
 */
function rules_scheduler_rules_action_info() {
  $items = array();
  // Add actions for all rule sets.
  foreach (rules_get_configured_items('rule_sets') as $name => $set_info) {
    $items[ 'rules_action_schedule_set_'. $name ] = $set_info + array(
      'module' => 'Rule Scheduler',
      'base' => 'rules_scheduler_action',
      'set' => $name,
    );
    $item =& $items[ 'rules_action_schedule_set_'. $name ];
    $item['label'] = t('Schedule @set', array('@set' => $set_info['label']));
    $item += array('arguments' => array());
    $date_arg = array('task_date' => array(
      'type' => 'date',
      'label' => t('Scheduled evaluation date'),
    ));
    $item['arguments'] = array_merge( $date_arg, $item['arguments']);
  }
  return $items;
}

/**
 * Base action implementation for scheduling all rule sets
 */
function rules_scheduler_action() {
  $args = func_get_args();
  $state = array_pop($args);
  $element = array_pop($args);
  $settings = array_pop($args);

  $date = array_shift($args);
  array_shift($element['#info']['arguments']);

  $pack = rules_pack_variables($element['#info']['arguments'], $args);

  if ($pack === FALSE) {
    rules_log(t('Packing arguments for scheduling the rule set %set failed.', array('%set' => $element['#info']['set'])), TRUE);
  }
  else {
    drupal_write_record('rules_scheduler', $task = array(
      'date' => $date,
      'set_name' => $element['#info']['set'],
      'arguments' => $pack,
    ));
  }
}

/**
 * Help for the base action implementation.
 */
function rules_scheduler_action_help() {
  return t("The evaluation of the rule set is going to be scheduled by cron. So make sure you have configured cron correctly by checking your site's !status.", array('!status' => l('Status report', 'admin/reports/status'))) .
    ' '. t('Also note that the scheduling time accuracy depends on your configured cron interval.');
}
