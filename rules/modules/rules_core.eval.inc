<?php
// $Id$

/**
 * @file
 * Contains rules core integration needed during evaluation.
 *
 * @addtogroup rules
 * @{
 */

/**
 * A class implementing a rules input evaluator processing date input. This is
 * needed to treat relative date inputs for strtotime right, consider "now".
 */
class RulesDateInputEvaluator extends RulesDataInputEvaluator {

  const DATE_REGEX_LOOSE = '/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2})?)?$/';

  public function prepare($text, $var_info) {
    if (is_numeric($text)) {
      // Let rules skip this input evaluators in case it's already a timestamp.
      $this->setting = NULL;
    }
  }

  public function evaluate($text, $options, RulesState $state) {
    return self::gmstrtotime($text);
  }

  /**
   * Convert a time string to a GMT (UTC) unix timestamp.
   */
  public static function gmstrtotime($date) {
    if (strpos($date, 'UTC') === FALSE) {
      // Append 'UTC' to indicate that the time string is in GMT/UTC.
      $date .= ' UTC';
    }
    return strtotime($date);
  }

  /**
   * Determine whether the given date string specifies a fixed date.
   */
  public static function isFixedDateString($date) {
    return is_string($date) && preg_match(self::DATE_REGEX_LOOSE, $date);
  }
}

/**
 * A class implementing a rules input evaluator processing URI inputs to make
 * sure URIs are absolute and path aliases get applied.
 */
class RulesURIInputEvaluator extends RulesDataInputEvaluator {

  public function prepare($uri, $var_info) {
    if (!isset($this->processor) && valid_url($uri, TRUE)) {
      // Only process if another evaluator is used or the url is not absolute.
      $this->setting = NULL;
    }
  }

  public function evaluate($uri, $options, RulesState $state) {
    if (!url_is_external($uri)) {
      // Extract the path so URL aliases are applied properly.
      $query = '';
      if (($pos = strpos($uri, '?')) !== FALSE) {
        $query = substr($uri, $pos);
        $uri = substr($uri, 0, $pos);
      }
      return url($uri, array('absolute' => TRUE) + $options) . $query;
    }
    elseif (valid_url($uri)) {
      return $uri;
    }
    throw new RulesException('Input evaluation generated an invalid URI.', array(), NULL, RulesLog::WARN);
  }
}

/**
 * A data processor for applying date offsets.
 */
class RulesDateOffsetProcessor extends RulesDataProcessor {

  protected static function form($settings, $var_info) {
    $settings += array('value' => '');
    $form = array(
      '#type' => 'fieldset',
      '#title' => t('Add offset'),
      '#collapsible' => TRUE,
      '#collapsed' => empty($settings['value']),
      '#description' => t('Add an offset to the selected date.'),
    );
    $form['value'] = array(
      '#type' => 'rules_duration',
      '#title' => t('Offset'),
      '#description' => t('Note that you can also specify negative numbers.'),
      '#default_value' => $settings['value'],
      '#weight' => 5,
    );
    return $form;
  }

  public function process($value, $info, RulesState $state, RulesPlugin $element) {
    $value = isset($this->processor) ? $this->processor->process($value, $info, $state, $element) : $value;
    return $value + $this->setting['value'];
  }
}
