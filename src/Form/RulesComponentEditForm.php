<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Ui\RulesUiConfigHandler;

/**
 * Provides a form to edit a component.
 */
class RulesComponentEditForm extends RulesComponentFormBase {

  /**
   * The RulesUI handler of the currently active UI.
   *
   * @var \Drupal\rules\Ui\RulesUiConfigHandler
   */
  protected $rulesUiHandler;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RulesUiConfigHandler $rules_ui_handler = NULL) {
    // Overridden such we can receive further route parameters.
    $this->rulesUiHandler = $rules_ui_handler;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    parent::prepareEntity();
    // Replace the config entity with the latest entity from temp store, so any
    // interim changes are picked up.
    $this->entity = $this->rulesUiHandler->getConfig();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = $this->rulesUiHandler->getForm()->buildForm($form, $form_state);
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->rulesUiHandler->getForm()->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save');
    $actions['cancel'] = [
      '#type' => 'submit',
      '#limit_validation_errors' => [['locked']],
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancel'],
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->rulesUiHandler->getForm()->submitForm($form, $form_state);

    // Persist changes by saving the entity.
    parent::save($form, $form_state);

    // Also remove the temporarily stored component, it has been persisted now.
    $this->rulesUiHandler->clearTemporaryStorage();

    drupal_set_message($this->t('Rule component %label has been updated.', ['%label' => $this->entity->label()]));
  }

  /**
   * Form submission handler for the 'cancel' action.
   */
  public function cancel(array $form, FormStateInterface $form_state) {
    $this->rulesUiHandler->clearTemporaryStorage();
    drupal_set_message($this->t('Canceled.'));
    $form_state->setRedirect('entity.rules_component.collection');
  }

  /**
   * Title callback: also display the rule label.
   */
  public function getTitle($rules_component) {
    return $this->t('Edit rules component "@label"', ['@label' => $rules_component->label()]);
  }

}
