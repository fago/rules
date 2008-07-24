<?php
// $Id$

/**
 * @file Rules input evaluation system.
 */

/**
 * Gets all available input evaluators, sorted by their weight.
 */
function rules_get_input_evaluators() {
  $evaluators = rules_gather_data('rules_evaluator');
  uasort($evaluators, '_rules_evaluator_sort');
  return $evaluators;
}

function _rules_evaluator_sort($a, $b) {
  return $a['weight'] < $b['weight'] ? -1 : ($a['weight'] > $b['weight'] ? 1 : 0);
}

/**
 * Prepares the available input evaluator on the element.
 */
function rules_prepare_input_evaluators(&$element, $variables) {

  $evaluators = rules_get_input_evaluators();
  $eval_settings = rules_get_settings_to_evaluate($element);
  $settings =& $element['#settings'];

  foreach ($evaluators as $base => $info) {
    // mark the evaluator to be used
    $settings['#eval input'][$base] = array();

    foreach ($eval_settings as $name) {
      if (isset($settings[$name]) && $settings[$name]) {
        // prepare the evaluation
        $data = call_user_func_array($base. '_prepare', array($settings[$name], $variables));
        if (isset($data)) {
          $settings['#eval input'][$base][$name] = $data;
        }
      }
    }
  }
}

function rules_get_settings_to_evaluate($element) {
  $info = rules_get_element_info($element);
  //TODO: include arguments
  return $info['eval input'];
}

/**
 * Applies the available input evaluator on the element.
 */
function rules_apply_input_evaluators(&$element, &$state) {
  $settings =& $element['#settings'];

  if (isset($settings['#eval input'])) {
    foreach ($settings['#eval input'] as $base => $info) {
      foreach ($info as $name => $data) {
        $settings[$name] = call_user_func_array($base. '_apply', array($settings[$name], $data, &$state));
      }
    }
  }
}

