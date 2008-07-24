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
  if (strpos($string, '<?') !== FALSE) {
    $used_vars = array();
    foreach ($variables as $name => $info) {
      if (strpos($string, '$'. $name) !== FALSE) {
        $used_vars[] = $name;
      }
    }
    return $used_vars;
  }
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
function rules_input_evaluator_php_help($variables, $save_info = FALSE) {
  $output = '<p>'.t('PHP code inside of &lt;?php ?&gt; delimiters will be evaluated and replaced by its output. E.g. &lt;? echo 1+1?&gt; will be replaced by 2.');
  $output .= ' '. t('Furthermore you can make use of the following variables:') . '</p>';
  $headers = array(t('Variable'), t('Type'), t('Description'), );
  if ($save_info) {
    $headers[] = t('Saveable');
  }
  $rows = array();
  foreach ($variables as $name => $info) {
    $row = array();
    $row[] = '$'. check_plain($name);
    $row[] = check_plain($info['type']);
    $row[] = check_plain($info['label']);
    if ($save_info) {
      if (isset($entity_info[$argument['#entity']]['#save']) && function_exists($entity_info[$argument['#entity']]['#save'])) {
        $row[] = (isset($argument['#saved']) && $argument['#saved']) ? t('Yes (Automatic)') : t('Yes');
      }
      else {
        $row[] = t('No');
      }
    }
    $rows[] = $row;
  }

  $output .= theme('table', $headers, $rows, array('class' => 'rules-php-help'));
  return $output;
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
