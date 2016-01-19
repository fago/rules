<?php

/**
 * @file
 * Contains \Drupal\rules\Form\BreakLockForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to break the lock of an edited rule.
 */
class BreakLockForm extends EntityConfirmFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The temporary storage factory.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The rendering service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, SharedTempStoreFactory $temp_store_factory, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('user.shared_tempstore'),
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
    return $this->t('Do you want to break the lock on rule %name?', ['%name' => $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $store = $this->tempStoreFactory->get($this->entity->getEntityTypeId());
    $locked = $store->getMetadata($this->entity->id());
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
    return $this->entity->urlInfo('edit-form');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get($this->entity->getEntityTypeId());
    if (!$store->getMetadata($this->entity->id())) {
      $form['message']['#markup'] = $this->t('There is no lock on rule %name to break.', ['%name' => $this->entity->id()]);
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get($this->entity->getEntityTypeId());
    $store->delete($this->entity->id());
    $form_state->setRedirectUrl($this->entity->urlInfo('edit-form'));
    drupal_set_message($this->t('The lock has been broken and you may now edit this rule.'));
  }

}
