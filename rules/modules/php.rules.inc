<?php
// $Id$
/**
 * @file rules integration for the php module
 * This provides an php input evaluator as well as a execute php code action.
 */

/**
 * Implementation of hook_rules_evaluator().
 */
function php_rules_evaluator() {
  return array(
    'rules_input_evaluator_php' => array(
      'label' => t('PHP Evaluation'),
      'weight' => 0,
    ),
  );
}

/**
 * Prepares the evalution.
 *
 * @param $string
 *   The string to evaluate later.
 * @param $variables
 *   An array of available variables.
 * @return
 *   Arbitrary data, which is passed to the evaluator on evaluation.
 *   If NULL is returned the input evaluator will be skipped later.
 */
function rules_input_evaluator_php_prepare($string, $variables) {
  $used_vars = array();
  foreach ($variables as $name => $info) {
    if (strpos($string, '$'. $name) !== FALSE) {
      $used_vars[] = $name;
    }
  }
  return $used_vars;
}

/**
 * Apply the input evaluator.
 *
 * @param $string
 *   The string to evaluate.
 * @param $data
 *   If the prepare method is used, the data as return from prepare().
 */
function rules_input_evaluator_php_apply($string, $data = NULL, &$state) {
  $vars = array();

  if ($data) {
    $vars = rules_get_variables($data, $state);
    if (!$vars) {
      rules_log(t('PHP code is not evaluated as there are not all used variables available.'));
      return $string;
    }
  }
  return rules_php_eval($string, $vars);
}

/**
 * Returns some help for the admin using the evaluator
 */
function rules_input_evaluator_php_help() {

}


/**
 * Evalutes the given PHP code, with the given variables defined. This is like
 * drupal_eval() but allows to pass some arguments to the code.
 *
 * @param $code The PHP code to run
 * @param $arguments Array containing variables to be extracted to the code
 * @return The return value of eval
 */
function rules_php_eval($code, $arguments = array()) {
  extract($arguments);

  ob_start();
  print eval('?>'. $code);
  $output = ob_get_contents();
  ob_end_clean();

  return $output;
}



/**
 * Implementation of hook_action_info()
 */
function php_rules_action_info() {
  return array(
    'rules_action_custom_php' => array(
      'label' => t('Execute custom PHP code'),
      'module' => 'PHP',
      'eval input' => array('code'),
    ),
  );
}

/**
 * Custom php action: Fires up the input evaluator manually and returns it's return value.
 */
function rules_action_custom_php($settings) {
  //print_r($settings);
  // Note that php code inside of <?php is executed by the input evaluator,
  // while this will execute the resulting string.
  //$vars = isset($settings['#eval input']['rules_input_evaluator_php']['code']) ? $settings['#eval input']['rules_input_evaluator_php']['code'] : array();
  //return rules_php_eval('<?'. $settings['code'], $vars);
}

function rules_action_custom_php_form($settings, &$form) {
  $form['settings']['code'] = array(
    '#type' => 'textarea',
    '#title' => t('PHP Code'),
    '#default_value' => isset($settings['code']) ? $settings['code'] : '',
    '#description' => t('The code that should be executed. To let rules save variables that support automatic saving just return an array of variables to save (e.g. return array("node" => $node);). Don\'t include &lt;?php ?&gt; delimiters.'),
    '#required' => TRUE,
  );
}
