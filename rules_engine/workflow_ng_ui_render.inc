<?php
/**
 * @file
 * Contains functions, that are used for rendering configurations with drupal_render()
 * If a configuration is viewed, it's passed to drupal_get_form so it is rendered
 */

/*
 * #Process handler for configurations
 * This is invoked by the form API. This function adds headlines for conditions and actions
 */
function workflow_ng_ui_prepare_configuration($element) {
  //generate ids for all conditions and actions, so we can identify them
  workflow_ng_element_id($element['#name'], $element);
  $element['#configuration'] = $element['#name'];
  //propagate the configuration name down the tree, as the name is needed for rendering the elements
  workflow_ng_propagate_configuration_name($element);

  //separate actions and conditions in separate arrays, so we can render them easier
  $render = _workflow_ng_ui_configuration_separate($element);

  $render['event'] = array('#weight' => -10, '#value' => '<strong class="event">'. t('Invoked on event') .'</strong>: '. check_plain(workflow_ng_get_events('labels', $element['#event'])));
  $render['event']['#prefix'] = '<p class="event">';
  $render['event']['#suffix'] = '</p>';
  workflow_ng_ui_element_help($render['event'], workflow_ng_get_events('all', $element['#event']));

  $render['condition_headline'] = array('#weight' => -6, '#value' => '<h3 class="conditions">'. t('Conditions') .'</h3>');
  $render['conditions']['#weight'] = 0;
  /* render the conditions of the configuration like an AND */
  $render['conditions'] += array('#theme' => 'workflow_ng_operation', '#label' => t('AND'), '#root' => TRUE);

  $render['actions_headline'] = array('#weight' => 10, '#value' => '<h3 class="actions">'. t('Actions') .'</h3>');
  $render['actions']['#weight'] = 20;

  $add_img = theme('workflow_ng_icon', 'add'). '';
  $render['condition_add'] = array('#weight' => 5, '#value' => $add_img . l(t('Add a condition'), WORKFLOW_NG_UI_PATH .'/'. $element['#name']. '/add/1/condition', array('class' => 'modify'), drupal_get_destination()), '#prefix' => '<p>', '#suffix' => '</p>');
  $render['action_add'] = array('#weight' => 30, '#value' => $add_img . l(t('Add an action'), WORKFLOW_NG_UI_PATH .'/'. $element['#name']. '/add/1/action', array('class' => 'modify'), drupal_get_destination()), '#prefix' => '<p>', '#suffix' => '</p>');
  return $render;
}

/*
 * Propagates the #configuration property down the tree
 */
function workflow_ng_propagate_configuration_name(&$elements) {
  foreach (element_children($elements) as $key) {
    $elements[$key]['#configuration'] = $elements['#configuration'];
    workflow_ng_propagate_configuration_name($elements[$key]);
  }
}

/*
 * Separates actions from the other elements
 */
function _workflow_ng_ui_configuration_separate($cfg) {
  $actions = array(); $conditions = array();
  foreach (element_children($cfg) as $key) {
    if (isset($cfg[$key]['#type']) && $cfg[$key]['#type'] == 'action') {
      $actions[$key] = $cfg[$key];
    }
    else {
      $conditions[$key] = $cfg[$key];
    }
  }
  return array('conditions' => $conditions, 'actions' => $actions);
}


/*
 * Renders a configuration
 */
function theme_configuration($element) {
  if ($element['#fixed']) {
    //this class will hide all edit/add/delete links..
    return '<div class="configuration-fixed">'. $element['#children'] .'</div>';
  }
  return $element['#children'];
}

/*
 * Renders a condition
 */
function theme_condition($element) {
  $element += workflow_ng_get_element_info($element);
  $attributes = isset($element['#attributes']) ? $element['#attributes'] : array();
  $title = theme('workflow_ng_icon', 'condition'). ' '. check_plain($element['#label']);
  $path = WORKFLOW_NG_UI_PATH .'/'. $element['#configuration']. '/edit/'. $element['#id'];
  $link = l($title, $path, _workflow_ng_attributes_add_class($attributes, 'condition'), drupal_get_destination(), NULL, FALSE, TRUE);

  $path = WORKFLOW_NG_UI_PATH .'/'. $element['#configuration']. '/add/'. $element['#id'] .'/op';
  $indent_link = l(theme_workflow_ng_icon('indent', t('Indent this condition by adding a logical operation.')), $path, _workflow_ng_attributes_add_class($attributes, 'condition_add'), drupal_get_destination(), NULL, FALSE, TRUE);

  $print_op = $element['#negate'] ? theme('workflow_ng_logical_operation_label', 'not', t('NOT')) .' ' : '';
  return $print_op . $link .' '. $indent_link;
}

/*
 * Renders an action
 */
function theme_action($element) {
  $element += workflow_ng_get_element_info($element);
  $attributes = isset($element['#attributes']) ? $element['#attributes'] : array();
  $title = theme('workflow_ng_icon', 'action'). ' '. check_plain($element['#label']);
  $path = WORKFLOW_NG_UI_PATH .'/'. $element['#configuration']. '/edit/'. $element['#id'];
  return l($title, $path, _workflow_ng_attributes_add_class($attributes, 'action'), drupal_get_destination(), NULL, FALSE, TRUE);
}

/*
 * Themes a icon
 */
function theme_workflow_ng_icon($name, $title = NULL) {
  $path = drupal_get_path('module', 'workflow_ng_ui') .'/icons/'. $name .'.png';
  return theme('image', $path, $name, $title ? $title : $name, array('class' => 'workflow-ng-icon'));
}

function _workflow_ng_attributes_add_class($attributes, $class) {
  if (isset($attributes['class'])) {
    $attributes['class'] .= ' '. $class;
  }
  else {
    $attributes['class'] = $class;
  }
  return $attributes;
}

/*
 * Themes the children of a logical operation.
 * Put the operation in between each children
 */
function theme_workflow_ng_operation($element) {
  if (count(element_children($element)) == 0 && !$element['#root']) {
    //no children, so drupal_render() won't render the element itself, so we do it ourself except for the condition root (AND)
    $element['#children'] = t('Empty');
    return theme('OR', $element);
  }
  //render the children and put the operation between them
  $content = array();
  foreach (element_children($element) as $key) {
    $content[] = drupal_render($element[$key]);
  }
  $print_op = theme('workflow_ng_logical_operation_label', strtolower($element['#label']), $element['#label']);
  return implode($print_op, $content);
}

/*
 * Themes the OR condition group
 */
function theme_OR($element) {
  $attributes = isset($element['#attributes']) ? $element['#attributes'] : array();
  $element['#attributes'] = _workflow_ng_attributes_add_class($attributes, 'workflow_ng_'. $element['#type']);

  $print_op = $element['#negate'] ? t('NOT') .' ' : '';
  $element['#title'] = t("!not%label group", array('!not' =>  $print_op , '%label' => $element['#label']));
  $element['#children'] .= '<p class="logical-op-add">'. theme('workflow_ng_icon', 'add'). l(t('Add another condition to this group'), WORKFLOW_NG_UI_PATH .'/'. $element['#configuration']. '/add/'. $element['#id'] .'/condition', array('class' => 'modify'), drupal_get_destination()). '</p>';
  $element['#children'] .= '<p class="logical-op-edit">'. theme('workflow_ng_icon', 'edit'). l(t('Edit this condition group'), WORKFLOW_NG_UI_PATH .'/'. $element['#configuration']. '/edit/'. $element['#id'], array('class' => 'modify'), drupal_get_destination()). '</p>';
  return theme('fieldset', $element);
}
/*
 * Themes the AND condition group
 */
function theme_AND($element) {
  return theme('OR', $element);
}
/*
 * Themes the operation label
 */
function theme_workflow_ng_logical_operation_label($op, $label) {
  return "<div class='logical-op-$op'>$label</div>";
}

