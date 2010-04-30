<?php
// $Id$

/**
 * @file Contains data type related forms.
 */


/**
 * Interface for data types providing a direct input form.
 */
interface RulesDataDirectInputForm {

  /**
   * Constructs the direct input form.
   *
   * @return Array
   *  The direct input form.
   */
  public static function inputForm($name, $info, $settings, RulesPlugin $element);

  /**
   * Render the configured value.
   *
   * @return Array
   *   A renderable array.
   */
  public static function render($value);
}

/**
 * Default UI related class for data types.
 */
class RulesDataUI {

  /**
   * Specifies the default input mode per data type.
   */
  public static function getDefaultMode() {
    return 'selector';
  }

  /**
   * Provides the selection form for a parameter.
   */
  public static function selectionForm($name, $info, $settings, RulesPlugin $element) {
    $form[$name . ':select'] = array(
      '#type' => 'textfield',
      '#title' => t('Data selector'),
      '#default_value' => isset($settings[$name . ':select']) ? $settings[$name . ':select'] : '',
      '#required' => empty($info['optional']),
    );
    $form['help'] = array(
      '#theme' => 'rules_data_selector_help',
      '#variables' => $element->availableVariables(),
      '#parameter' => $info,
    );

    // Add data processor.
    $settings += array($name . ':process' => array());
    $form[$name . ':process'] = array();
    RulesDataProcessor::attachForm($form[$name . ':process'], $settings[$name . ':process'], $info, $element->availableVariables());
    return $form;
  }
}

/**
 * UI for textual data.
 */
class RulesDataUIText extends RulesDataUI implements RulesDataDirectInputForm {

  public static function getDefaultMode() {
    return 'input';
  }

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    if (!empty($info['options list'])) {
      // Make sure the .rules.inc of the providing module is included as the
      // options list callback may reside there.
      $element->call('loadBasicInclude');
      $form[$name] = array(
        '#type' => 'select',
        '#options' => $info['options list']($element),
      );
    }
    else {
      $form[$name] = array(
        '#type' => 'textarea',
      );
      RulesDataInputEvaluator::attachForm($form, $settings, $info, $element->availableVariables());
    }
    $form[$name] += array(
      '#title' => t('Value'),
      '#default_value' => isset($settings[$name]) ? $settings[$name] : '',
      '#required' => empty($info['optional']),
    );
    return $form;
  }

  public static function render($value) {
    return array(
      'content' => array('#markup' => check_plain($value)),
      '#attributes' => array('class' => array('rules-parameter-text')),
    );
  }

}

/**
 * UI for decimal data.
 */
class RulesDataUIDecimal extends RulesDataUIText {

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $form = parent::inputForm($name, $info, $settings, $element);
    $form[$name]['#type'] = 'textfield';
    $form[$name]['#element_validate'][] = 'rules_ui_element_decimal_validate';
    return $form;
  }
}

/**
 * UI for integers.
 */
class RulesDataUIInteger extends RulesDataUIText {

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $form = parent::inputForm($name, $info, $settings, $element);
    $form[$name]['#type'] = 'textfield';
    $form[$name]['#element_validate'][] = 'rules_ui_element_integer_validate';
    return $form;
  }
}

/**
 * UI for boolean data.
 */
class RulesDataUIBoolean extends RulesDataUI implements RulesDataDirectInputForm {

  public static function getDefaultMode() {
    return 'input';
  }

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $form[$name] = array(
      '#type' => 'checkbox',
      '#title' => check_plain($info['label']),
      '#default_value' => !empty($settings[$name]),
    );
    return $form;
  }

  public static function render($value) {
    return array(
      'content' => array('#markup' => !empty($value) ? t('true') : t('false')),
      '#attributes' => array('class' => array('rules-parameter-boolean')),
    );
  }
}

/**
 * UI for dates.
 */
class RulesDataUIDate extends RulesDataUIText {

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $settings += array($name => gmdate('Y-m-d H:i:s', time()));
    // Convert any configured timestamp into a readable format.
    if (is_numeric($settings[$name])) {
      $settings[$name] = gmdate('Y-m-d H:i:s', $settings[$name]);
    }
    $form = parent::inputForm($name, $info, $settings, $element);
    $form[$name]['#type'] = 'textfield';
    $form[$name]['#element_validate'][] = 'rules_ui_element_date_validate';
    // Note that the date input evaluator takes care for parsing dates using
    // strtotime() into a timestamp, which is the internal date format.
    $form[$name]['#description'] = t('The date in GMT. Format: %format or other values in GMT known by the PHP !strtotime function like "+1 day". Relative dates like "+1 day" or "now" relate to the evaluation time.', array('%format' => gmdate('Y-m-d H:i:s', time() + 86400), '!strtotime' => l('strtotime()', 'http://php.net/strtotime')));

    //TODO: Leverage the jquery datepicker+timepicker once a module providing
    //the timpeicker is available.
    return $form;
  }

  public static function render($value) {
    $value = is_numeric($value) ? format_date($value, 'short') : check_plain($value);
    return array(
      'content' => array('#markup' => $value),
      '#attributes' => array('class' => array('rules-parameter-date')),
    );
  }
}

/**
 * UI for duration type parameter.
 */
class RulesDataUIDuration extends RulesDataUIText {

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $form = parent::inputForm($name, $info, $settings, $element);
    $form[$name]['#type'] = 'rules_duration';
    return $form;
  }

  public static function render($value) {
    $value = is_numeric($value) ? format_interval($value) : check_plain($value);
    return array(
      'content' => array('#markup' => $value),
      '#attributes' => array('class' => array('rules-parameter-duration')),
    );
  }
}

/**
 * UI for the URI type parameter.
 */
class RulesDataUIURI extends RulesDataUIText {

  public static function inputForm($name, $info, $settings, RulesPlugin $element) {
    $form = parent::inputForm($name, $info, $settings, $element);
    $form[$name]['#rows'] = 2;
    return $form;
  }

}
