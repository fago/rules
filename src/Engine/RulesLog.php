<?php

/**
 * @file
 * Contains \Drupal\rules\RulesLog.
 */

namespace Drupal\rules\Engine;
use Drupal\rules\Exception;

/**
 * The rules default logging class.
 */
class RulesLog {

  const INFO  = 1;
  const WARN  = 2;
  const ERROR = 3;

  static protected $logger;

  /**
   * @return RulesLog
   *   Returns the rules logger instance.
   */
  public static function logger($log_level = self::INFO) {
    if (!isset(self::$logger)) {
      $class = __CLASS__;
      self::$logger = new $class($log_level);
    }
    return self::$logger;
  }

  protected $log = [];
  protected $logLevel;
  protected $line = 0;

  /**
   * This is a singleton.
   */
  protected function __construct($logLevel = self::WARN) {
    $this->logLevel = $logLevel;
  }

  public function __clone() {
    throw new Exception("Cannot clone the logger.");
  }

  /**
   * Logs a log message.
   *
   * @see rules_log()
   */
  public function log($msg, $args = [], $logLevel = self::INFO, $scope = NULL, $path = NULL) {
    if ($logLevel >= $this->logLevel) {
      $this->log[] = [$msg, $args, $logLevel, microtime(TRUE), $scope, $path];
    }
  }

  /**
   * Checks the log and throws an exception if there were any problems.
   */
  public function checkLog($logLevel = self::WARN) {
    foreach ($this->log as $entry) {
      if ($entry[2] >= $logLevel) {
        throw new Exception($this->render());
      }
    }
  }

  /**
   * Checks the log for (error) messages with a log level equal or higher than the given one.
   *
   * @return
   *   Whether the an error has been logged.
   */
  public function hasErrors($logLevel = self::WARN) {
    foreach ($this->log as $entry) {
      if ($entry[2] >= $logLevel) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Gets an array of logged messages.
   */
  public function get() {
    return $this->log;
  }

  /**
   * Renders the whole log.
   */
  public function render() {
    $line = 0;
    $output = [];
    while (isset($this->log[$line])) {
      $vars['head'] = t($this->log[$line][0], $this->log[$line][1]);
      $vars['log'] = $this->renderHelper($line);
      $output[] = theme('rules_debug_element', $vars);
      $line++;
    }
    return implode('', $output);
  }

  /**
   * Renders the log of one event invocation.
   */
  protected function renderHelper(&$line = 0) {
    $startTime = isset($this->log[$line][3]) ? $this->log[$line][3] : 0;
    $output = [];
    while ($line < count($this->log)) {
      if ($output && !empty($this->log[$line][4])) {
        // The next entry stems from another evaluated set, add in its log
        // messages here.
        $vars['head'] = t($this->log[$line][0], $this->log[$line][1]);
        if (isset($this->log[$line][5])) {
          $vars['link'] = '[' . l('edit', $this->log[$line][5]) . ']';
        }
        $vars['log'] = $this->renderHelper($line);
        $output[] = theme('rules_debug_element', $vars);
      }
      else {
        $formatted_diff = round(($this->log[$line][3] - $startTime) * 1000, 3) .' ms';
        $msg = $formatted_diff .' '. t($this->log[$line][0], $this->log[$line][1]);
        if ($this->log[$line][2] >= RulesLog::WARN) {
          $level = $this->log[$line][2] == RulesLog::WARN ? 'warn' : 'error';
          $msg = '<span class="rules-debug-' . $level . '">'. $msg .'</span>';
        }
        if (isset($this->log[$line][5]) && !isset($this->log[$line][4])) {
          $msg .= ' [' . l('edit', $this->log[$line][5]) . ']';
        }
        $output[] = $msg;

        if (isset($this->log[$line][4]) && !$this->log[$line][4]) {
          // This was the last log entry of this set.
          return theme('item_list', ['items' => $output]);
        }
      }
      $line++;
    }
    return theme('item_list', ['items' => $output]);
  }

  /**
   * Clears the logged messages.
   */
  public function clear() {
    $this->log = [];
  }
}
