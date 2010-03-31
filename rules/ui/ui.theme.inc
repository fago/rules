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
  $form['#attached']['css'] = array(drupal_get_path('module', 'rules') . '/ui/rules.ui.css');

  foreach (element_children($form) as $element_id) {
    $element = &$form[$element_id];

    // Add special classes to be used for tabledrag.js.
    $element['parent_id']['#attributes']['class'] = array('rules-parent-id');
    $element['element_id']['#attributes']['class'] = array('rules-element-id');
    $element['weight']['#attributes']['class'] = array('rules-element-weight');

    $element['label']['label']['#prefix'] = '<div>';
    $element['label']['label']['#suffix'] = '</div>';

    $element['label']['config']['#prefix'] = '<div class="description">';
    $element['label']['config']['#suffix'] = '</div>';

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
