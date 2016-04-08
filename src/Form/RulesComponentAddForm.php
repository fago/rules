<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a form to add a component.
 */
class RulesComponentAddForm extends RulesComponentFormBase {

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    // Overridden to customize creation of new entities.
    if ($route_match->getRawParameter($entity_type_id) !== NULL) {
      $entity = $route_match->getParameter($entity_type_id);
    }
    else {
      $values = [];
      // @todo: Create the right expression depending on the route.
      $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
      $entity->setExpression($this->expressionManager->createRule());

    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    drupal_set_message($this->t('Component %label has been created.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.rules_component.edit_form', ['rules_component' => $this->entity->id()]);
  }

}
