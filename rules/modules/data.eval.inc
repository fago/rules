<?php
// $Id$

/**
 * @file
 * Contains often used rules data wrappers
 *
 * @addtogroup rules
 * @{
 */

/**
 * Action: Modify data.
 */
function rules_action_data_set($data, $value, $settings, $state, $element) {
  $wrapper = $state->currentArguments['data'];
  try {
    // Update the value first then save changes, if possible.
    $wrapper->set($value);
  }
  catch (EntityMetadataWrapperException $e) {
    throw new RulesException('Unable to modify data "@selector": ' . $e->getMessage(), array('@selector' => $settings['data:select']));
  }
  $info = $wrapper->info();
  // We always have to save the changes in the parent entity. E.g. when the node
  // author is changed, we don't want to save the author but the node. So use
  // the parent wrapper and also cut off the last part of the selector.
  $state->saveChanges(implode(':', explode(':', $settings['data:select'], -1)), $info['parent']);
}

/**
 * Action: Fetch data.
 */
function rules_action_entity_fetch($type, $id, $revision) {
  $info = entity_get_info($type);

  // Support the revision parameter, if applicable.
  if (isset($info['entity keys']['revision']) && isset($revision)) {
    $conditions = array($info['entity keys']['revision'] => $revision);
  }
  $return = entity_load($type, array($id), isset($conditions) ? $conditions : array());
  $entity = reset($return);
  if (!$entity) {
    throw new RulesException('Unable to load @entity with id "@id"', array('@id' => $id, '@entity' => $type));
  }
  return array('entity_fetched' => $entity);
}

/**
 * Action: Query entities.
 */
function rules_action_entity_query($type, $property, $value, $limit) {
  $return = entity_metadata_entity_query($type, $property, $value, $limit);
  return array('entity_fetched' => array_values($return));
}

/**
 * Action: Create entities.
 */
function rules_action_entity_create($args, $element) {
  $values = array();
  foreach (array_keys($args['settings']['#info']['parameter']) as $name) {
    // Remove the parameter name prefix 'param_'.
    $values[substr($name, 6)] = $args[$name];
  }
  $data = entity_metadata_entity_create($args['type'], $values)->value();
  return array('entity_created' => $data);
}

/**
 * Action: Save entities.
 */
function rules_action_entity_save($data, $immediate = FALSE, $settings, $state, $element) {
  $wrapper = $state->currentArguments['data'];
  $state->saveChanges($settings['data:select'], $wrapper, $immediate);
}

/**
 * Action: Delete entities.
 */
function rules_action_entity_delete($data, $settings, $state, $element) {
  $wrapper = $state->currentArguments['data'];
  $wrapper->delete();
}

/**
 * Action: Add a list item
 */
function rules_action_data_list_add($list, $item, $pos = 'end', $settings) {
  switch ($pos) {
    case 'start':
      array_unshift($list, $item);
      break;

    default:
      $list[] = $item;
      break;
  }
  return array('list' => $list);
}

/**
 * Action: Remove a list item
 */
function rules_action_data_list_remove($list, $item) {
  foreach (array_keys($list, $item) as $key) {
    unset($list[$key]);
  }
  return array('list' => $list);
}

/**
 * Condition: Compare data
 */
function rules_condition_data_is($data, $op, $value) {
  switch ($op) {
    default:
    case '==':
      return $data == $value || is_array($value) && in_array($data, $value);
    case '<':
      return $data < $value;
    case '>':
      return $data > $value;
    case 'contains':
      return is_string($data) && strpos($data, $value) !== FALSE || is_array($data) && in_array($value, $data);
  }
}

/**
 * Condition: Entity is new
 */
function rules_condition_entity_is_new($entity, $settings, $state, $element) {
  $wrapper = $state->currentArguments['entity'];
  return !$wrapper->getIdentifier() || !empty($entity->is_new);
}

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
