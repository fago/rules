<?php
// $Id$
/**
 * @file Forsm for the integration provided by rules
 */

/**
 * Condition: Textual comparision configuration form
 */
function rules_condition_text_compare_form($settings = array(), &$form) {
  $form['settings']['text1']['#title'] = t('Two texts to compare');
  $form['settings']['text2']['#title'] = '';
  $form['settings']['regex'] = array(
    '#type' => 'checkbox',
    '#title' => t('Evaluate the second text as a regular expression.'),
    '#default_value' => isset($settings['regex']) ? $settings['regex'] : 0,
  );
}

/**
 * Condition: Numerical comparision configuration form
 */
function rules_condition_number_compare_form($settings = array(), &$form) {
  $form['settings']['number1']['#weight'] = -2;
  $form['settings']['number2']['#weight'] = 2;

  $form['settings']['operation'] = array(
    '#type' => 'select',
    '#title' => t('Operation'),
    '#options' => array('greater' => t('Greater than'), 'equal' => t('Equal to'), 'less' => t('Less than')),
    '#default_value' => isset($settings['operation']) ? $settings['operation'] : 'equal',
  );
}

/**
 * The following functions help converting actions
 * when upgrading from workflow-ng
 */
function workflow_ng_condition_token_compare_upgrade(&$element) {
  $element['#name'] = 'rules_condition_text_compare';
}
function workflow_ng_condition_token_numeric_upgrade(&$element) {
  $element['#name'] = 'rules_condition_number_compare';
  foreach (array('text1', 'text2', 'text1_args', 'text2_args') as $key) {
    $element['#settings'][ str_replace('text', 'number', $key) ] = $element['#settings'][$key];
    unset($element['#settings'][$key]);
  }
}