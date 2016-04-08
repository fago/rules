<?php

namespace Drupal\rules\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\rules\Ui\RulesUiHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to break the lock of an edited rule.
 */
class BreakLockForm extends ConfirmFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The RulesUI handler of the currently active UI.
   *
   * @var \Drupal\rules\Ui\RulesUiHandlerInterface
   */
  protected $rulesUiHandler;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_break_lock_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to break the lock on %label?', ['%label' => $this->rulesUiHandler->getComponentLabel()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $locked = $this->rulesUiHandler->getLockMetaData();
    $account = $this->entityTypeManager->getStorage('user')->load($locked->owner);
    $username = [
      '#theme' => 'username',
      '#account' => $account,
    ];
    return $this->t('By breaking this lock, any unsaved changes made by @user will be lost.', [
      '@user' => $this->renderer->render($username),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->rulesUiHandler->getBaseRouteUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Break lock');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RulesUiHandlerInterface $rules_ui_handler = NULL) {
    $this->rulesUiHandler = $rules_ui_handler;
    if (!$rules_ui_handler->isLocked()) {
      $form['message']['#markup'] = $this->t('There is no lock on %label to break.', ['%label' => $rules_ui_handler->getComponentLabel()]);
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->rulesUiHandler->clearTemporaryStorage();
    $form_state->setRedirectUrl($this->rulesUiHandler->getBaseRouteUrl());
    drupal_set_message($this->t('The lock has been broken and you may now edit this @component_type.', [
      '@component_type' => $this->rulesUiHandler->getPluginDefinition()->component_type_label,
    ]));
  }

}
