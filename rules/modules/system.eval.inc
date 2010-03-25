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
function rules_action_drupal_message($message, $error) {
  drupal_set_message($message, $error ? 'error' : 'status');
}

/**
 * Action: Set breadcrumb.
 */
function rules_action_set_breadcrumb($titles, $paths) {
  $trail = array(l(t('Home'), ''));
  for ($i = 0; $i < count($titles); $i++) {
    // Skip empty titles
    if ($title = trim($titles[$i])) {
      // Output plaintext instead of a link if there is a title
      // without a path.
      $path = trim($paths[$i]);
      if (strlen($path) > 0 && $path != '<none>') {
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
 * Action: Page redirect.
 */
function rules_action_drupal_goto($url, $immediate = TRUE, $force = FALSE) {
  if ($force) {
    if (strpos($query, 'destination') === FALSE && isset($_REQUEST['destination'])) {
      // Keep the current destination parameter if there is one set.
      if (!empty($query)) {
        $query .= '&';
      }
      $query .= 'destination='. urlencode($_REQUEST['destination']);
    }
    unset($_REQUEST['destination']);
  }
  if ($immediate) {
    drupal_goto($url);
  }
  else {
    // If someone else issues a drupal_goto(), we hijack it by setting the destination parameter
    // But obey any already set destination parameter.
    if (!isset($_REQUEST['destination'])) {
      $_REQUEST['destination'] = urlencode($path) . ($query ? '?'. urlencode($query) : '') . ($fragment ? '#'. urlencode($fragment) : '');
    }
    // This lets _rules_action_drupal_goto_handler() invoke drupal_goto before the page is output.
    $GLOBALS['_rules_action_drupal_goto_do'] = TRUE;
  }
}

/**
 * Page template preprocess handler for the drupal goto action.
 *
 * @see rules_theme_registry_alter().
 */
function _rules_action_drupal_goto_handler($variables) {
  if (isset($GLOBALS['_rules_action_drupal_goto_do'])) {
    unset($GLOBALS['_rules_action_drupal_goto_do']);
    // We have already set $_REQUEST['destination'] which drupal_goto() is going to respect.
    drupal_goto('');
  }
}

/**
 * A class implementing a rules input evaluator processing tokens.
 */
class RulesTokenEvaluator extends RulesDataInputEvaluator {

  public static function access() {
    return TRUE;
  }

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
    // Fix to properly support 'site' tokens.
    $var_info['site']['type'] = 'site';
    foreach (token_scan($text) as $var_name => $tokens) {
      if ($var_name != 'site') {
        $key = $var_info[$var_name]['type'];
        $data = rules_unwrap_data(array($key => $state->get($var_name)), array($key => $var_info[$var_name]));
      }
      $replacements += token_generate($var_info[$var_name]['type'], $tokens, $data, $options);
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
    $var_info['site'] = array('label' => t('Site'), 'type' => 'site');
    $token_types['site'] = 'site';

    foreach ($token_types as $name => $token_type) {
      if (isset($token_info['types'][$token_type])) {
        $render[$name] = array(
          '#theme' => 'table',
          '#header' => array(t('Token'), t('Label'), t('Description')),
          '#prefix' => '<h3>' . t('Replacement patterns for %label', array('%label' => $var_info[$name]['label'])) . '</h3>',
        );
        foreach ($token_info['tokens'][$token_type] as $token => $info) {
          $token = $var_info[$name]['type'] . ':' . $token;
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
