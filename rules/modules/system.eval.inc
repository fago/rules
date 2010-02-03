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
 * Wraps system log entries of hook_watchdog().
 */
class RulesDataLogEntryWrapper extends RulesDataWrapper implements RulesDataMetadataWrapperInterface {

  /**
   * Specify metadata for the log entry and return a metadata wrapper.
   */
  public function metadataWrapper(array $state = NULL) {
    if (!isset($this->wrapper)) {
      $data = &$this->get($state);
      // The info has to contain 'data info' which we pass through.
      $info = array('data info' => $this->info['data info']);
      $this->wrapper = entity_metadata_wrapper($this->type, $data, $info);
    }
    return $this->wrapper;
  }
}

/**
 * Getter callback for the log entry message property.
 */
function rules_system_log_get_message($log_entry) {
  return t($log_entry['message'], $log_entry['variables']);
}

/**
 * @}
 */
