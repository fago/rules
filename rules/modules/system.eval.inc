<?php
// $Id$

/**
 * @file
 * Contains rules integration for the system module needed during evaluation.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Action: Show a drupal message.
 */
function rules_action_drupal_message($message, $status, $repeat) {
  drupal_set_message(filter_xss_admin($message), $status, $repeat);
}

/**
 * Action: Page redirect.
 *
 * @see rules_page_build()
 */
function rules_action_drupal_goto($url, $force = FALSE, $destination = FALSE) {
  // Keep the current destination parameter if there is one set.
  if ($destination) {
    $url .= strpos($url, '?') === FALSE ? '?' : '&';
    $url .= drupal_http_build_query(drupal_get_destination());
  }
  // We set the destination parameter which will be applied by drupal_goto().
  if ($force || !isset($_GET['destination'])) {
    // This does not work any more as $url is absolute.
    // @todo: Fix once #732542 is resolved.
    $_GET['destination'] = $url;
  }
  // This makes sure invoke rules_page_build() invokes drupal_goto() before
  // the page is output. We don't invoke it right now, as this would end the
  // the current page execution unpredictly for modules.
  $GLOBALS['_rules_action_drupal_goto_do'] = $url;
}

/**
 * Action: Set breadcrumb.
 */
function rules_action_breadcrumb_set(array $titles, array $paths) {
  $trail = array(l(t('Home'), ''));
  foreach ($titles as $i => $title) {
    // Skip empty titles.
    if ($title = trim($title)) {
      // Output plaintext instead of a link if there is a title
      // without a path.
      $path = trim($paths[$i]);
      if (!empty($paths[$i]) && $paths[$i] != '<none>') {
        $trail[] = l($title, trim($paths[$i]));
      }
      else {
        $trail[] = check_plain($title);
      }
    }
  }
  drupal_set_breadcrumb($trail);
}

/**
 * A class implementing a rules input evaluator processing tokens.
 */
class RulesTokenEvaluator extends RulesDataInputEvaluator {

  public function prepare($text, $var_info) {
    // Skip this evaluator if there are no tokens.
    $this->setting = token_scan($text) ? TRUE : NULL;
  }

  /**
   * We replace the tokens on our own as we cannot use token_replace(), because
   * token usually assumes that $data['node'] is a of type node, which doesn't
   * hold in general in our case.
   * So we properly map variable names to variable data types and then run the
   * replacement ourself.
   */
  public function evaluate($text, $options, RulesState $state) {
    $var_info = $state->varInfo();
    $options += array('sanitize' => FALSE);

    $replacements = array();
    $data = array();
    foreach (token_scan($text) as $var_name => $tokens) {
      if (isset($var_info[$var_name])) {
        $key = $var_info[$var_name]['type'];
        $data = rules_unwrap_data(array($key => $state->get($var_name)), array($key => $var_info[$var_name]));
        $replacements += token_generate($var_info[$var_name]['type'], $tokens, $data, $options);
      }
      else {
        $replacements += token_generate($var_name, $tokens, array(), $options);
      }
    }

    // Optionally clean the list of replacement values.
    if (!empty($options['callback']) && function_exists($options['callback'])) {
      $function = $options['callback'];
      $function($replacements, $data, $options);
    }

    $tokens = array_keys($replacements);
    $values = array_values($replacements);
    return str_replace($tokens, $values, $text);
  }

  public static function help($var_info) {
    $render = array(
      '#type' => 'fieldset',
      '#title' => t('Replacement patterns'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $token_info = token_info();
    foreach ($var_info as $name => $info) {
      $token_types[$name] = isset($info['token type']) ? $info['token type'] : $info['type'];
    }

    foreach ($token_types as $name => $token_type) {
      if (isset($token_info['types'][$token_type])) {
        $render[$name] = array(
          '#theme' => 'table',
          '#header' => array(t('Token'), t('Label'), t('Description')),
          '#prefix' => '<h3>' . t('Replacement patterns for %label', array('%label' => $var_info[$name]['label'])) . '</h3>',
        );
        foreach ($token_info['tokens'][$token_type] as $token => $info) {
          $token = '[' . $var_info[$name]['type'] . ':' . $token . ']';
          $render[$name]['#rows'][$token] = array(check_plain($token), check_plain($info['name']), check_plain($info['description']));
        }
      }
    }
    return $render;
  }
}

/**
 * @}
 */
