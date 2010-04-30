<?php
// $Id$

/**
 * @file Rules theme functions
 */


/**
 * Themes a tree of rule elements in a draggable table.
 *
 * @ingroup themeable
 */
function theme_rules_elements($variables) {
  $form = $variables['element'];
  $form['#theme'] = 'table';
  $form['#header'] = array($form['#heading'], t('Weight'), t('Operations'));
  $form['#attributes']['id'] = 'rules-' . drupal_html_id($form['#heading']) . '-id';

  foreach (element_children($form) as $element_id) {
    $element = &$form[$element_id];

    // Add special classes to be used for tabledrag.js.
    $element['parent_id']['#attributes']['class'] = array('rules-parent-id');
    $element['element_id']['#attributes']['class'] = array('rules-element-id');
    $element['weight']['#attributes']['class'] = array('rules-element-weight');

    $row = array();
    $row[] = theme('indentation', array('size' => $element['#depth'])) . drupal_render($element['label']);

    $row[] = drupal_render($element['weight']) . drupal_render($element['parent_id']) . drupal_render($element['element_id']);
    $row[] = array('class' => 'rules-operations', 'data' => drupal_render($element['operations']));

    $row = array('data' => $row) + $element['#attributes'];
    $row['class'][] = 'draggable';
    if (!$element['#container']) {
      $row['class'][] = 'tabledrag-leaf';
    }
    $form['#rows'][] = $row;
  }
  if (!empty($form['#rows'])) {
    drupal_add_tabledrag($form['#attributes']['id'], 'match', 'parent', 'rules-parent-id', 'rules-parent-id', 'rules-element-id', TRUE, 10);
    drupal_add_tabledrag($form['#attributes']['id'], 'order', 'sibling', 'rules-element-weight');
  }
  return drupal_render($form);
}

/**
 * Themes a view of multiple configuration items.
 * @ingroup themeable
 */
function theme_rules_content_group($variables) {
  $element = $variables['element'];
  foreach (element_children($element) as $key) {
    $output[] = drupal_render($element[$key]);
  }
  $heading = !empty($element['#heading']) ? "<span class='rules-content-heading'>" . $element['#heading'] . ': </span>' : '';
  return !empty($output) ? $heading . implode(', ', $output) : '';
}

/**
 * Themes the view of a single parameter configuration.
 * @ingroup themeable
 */
function theme_rules_parameter_configuration($variables) {
  $element = $variables['element'];
  $content = drupal_render_children($element);
  // Add the full content to the span's title, but don't use drupal_attributes
  // for that as this would invoke check_plain() again.
  $title = strip_tags($content);
  $element['#attributes']['class'][] = 'rules-parameter-configuration';
  $attributes = drupal_attributes($element['#attributes']) . " title='$title'";

  $label_attributes['class'][] = 'rules-parameter-label';
  if (!empty($element['#info']['description'])) {
    $label_attributes['title'] = $element['#info']['description'];
  }
  $label_attributes = drupal_attributes($label_attributes);

  $output = "<span $label_attributes>" . check_plain($element['#info']['label']) . ': </span>';
  $output .= "<span $attributes>" . truncate_utf8($content, 30, TRUE, TRUE) . "</span>";
  return $output;
}

/**
 * Themes help for using the data selector.
 * @ingroup themeable
 */
function theme_rules_data_selector_help($variables) {
  $variables_info = $variables['variables'];
  $param_info = $variables['parameter'];

  $render = array(
    '#type' => 'fieldset',
    '#title' => t('Data selectors'),
    '#pre_render' => array(),
    '#attributes' => array(),
  );
  $render['table'] = array(
    '#theme' => 'table',
    '#header' => array(t('Selector'), t('Label'), t('Description')),
  );
  foreach (RulesData::matchingDataSelector($variables_info, $param_info) as $selector => $info) {
    $info += array('label' => '', 'description' => '');
    $render['table']['#rows'][] = array(check_plain($selector), check_plain(drupal_ucfirst($info['label'])), check_plain($info['description']));
  }
  return drupal_render($render);
}
