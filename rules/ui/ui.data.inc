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
   * @return Array
   *  The direct input form.
   */
  public function inputForm($name, $info, $settings, RulesPlugin $element);
}

/**
 * Default UI related class for data types.
 */
class RulesDataUI {

  /**
   * Specifies the default input mode per data type.
   */
  public function getDefaultMode() {
    return 'selector';
  }

  /**
   * Provides the selection form for a parameter.
   */
  public function selectionForm($name, $info, $settings, RulesPlugin $element) {
    $form[$name . ':select'] = array(
      '#type' => 'textfield',
      '#title' => t('Data selector'),
      '#default_value' => isset($settings[$name . ':select']) ? $settings[$name . ':select'] : '',
      '#required' => empty($info['optional']),
    );
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

  public function getDefaultMode() {
    return 'input';
  }

  public function inputForm($name, $info, $settings, RulesPlugin $element) {
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
}

/**
 * UI for decimal data.
 */
class RulesDataUIDecimal extends RulesDataUIText {

  public function inputForm($name, $info, $settings, RulesPlugin $element) {
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

  public function inputForm($name, $info, $settings, RulesPlugin $element) {
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

  public function getDefaultMode() {
    return 'input';
  }

  public function inputForm($name, $info, $settings, RulesPlugin $element) {
    $form[$name] = array(
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#default_value' => !empty($settings[$name]),
    );
    return $form;
  }
}

