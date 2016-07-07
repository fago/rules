<?php

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Ui\RulesUiHandlerInterface;

/**
 * Components form, ready to be embedded in some other form.
 *
 * Note, that there is no SubformInterface or such in core (yet), thus we
 * implement FormInterface instead.
 */
class EmbeddedComponentForm implements FormInterface {

  /**
   * The RulesUI handler of the currently active UI.
   *
   * @var \Drupal\rules\Ui\RulesUiHandlerInterface
   */
  protected $rulesUiHandler;

  /**
   * Constructs the object.
   *
   * @param \Drupal\rules\Ui\RulesUiHandlerInterface $rules_ui_handler
   *   The UI handler of the edited component.
   */
  public function __construct(RulesUiHandlerInterface $rules_ui_handler) {
    $this->rulesUiHandler = $rules_ui_handler;
  }

  /**
   * Gets the form handler for the component's expression.
   *
   * @return \Drupal\rules\Form\Expression\ExpressionFormInterface|null
   *   The form handling object if there is one, NULL otherwise.
   */
  protected function getFormHandler() {
    return $this->rulesUiHandler
      ->getComponent()
      ->getExpression()
      ->getFormHandler();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_embedded_component_' . $this->rulesUiHandler->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['locked'] = $this->rulesUiHandler->addLockInformation();
    return $this->getFormHandler()->form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->rulesUiHandler->validateLock($form, $form_state);
    $this->getFormHandler()->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->getFormHandler()->submitForm($form, $form_state);
  }

}
