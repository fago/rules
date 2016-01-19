<?php

/**
 * @file
 * Contains \Drupal\rules\Form\TempStoreTrait.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\SharedTempStoreFactory;

/**
 * Provides methods for modified rules configurations in temporary storage.
 */
trait TempStoreTrait {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The temporary store for the rules configuration.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Retrieves the temporary storage service if not already present.
   *
   * @return \Drupal\user\SharedTempStoreFactory
   *   The factory.
   */
  protected function getTempStoreFactory() {
    if (!isset($this->tempStoreFactory)) {
      $this->tempStoreFactory = \Drupal::service('user.shared_tempstore');
    }
    return $this->tempStoreFactory;
  }

  /**
   * Setter injection for the temporary storage factory.
   *
   * @param \Drupal\user\SharedTempStoreFactory $temp_store_factory
   *   The factory.
   */
  public function setTempStoreFactory(SharedTempStoreFactory $temp_store_factory) {
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * Retrieves the date formatter service if not already present.
   *
   * @return \Drupal\Core\Datetime\DateFormatterInterface
   *   The service.
   */
  protected function getDateFormatter() {
    if (!isset($this->dateFormatter)) {
      $this->dateFormatter = \Drupal::service('date.formatter');
    }
    return $this->dateFormatter;
  }

  /**
   * Setter injection for the date formatter service.
   *
   * @param \Drupal\rules\Form\DateFormatterInterface $date_formatter
   *   The service.
   */
  public function setDateFormatter(DateFormatterInterface $date_formatter) {
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Retrieves the entity type manager service if not already present.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function getEntityTypeManager() {
    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = \Drupal::service('entity_type.manager');
    }
    return $this->entityTypeManager;
  }

  /**
   * Gets the temporary storage repository from the factory.
   *
   * @return \Drupal\user\SharedTempStore
   *   The shareds storage.
   */
  protected function getTempStore() {
    if (!isset($this->tempStore)) {
      $this->tempStore = $this->getTempStoreFactory()->get($this->getRuleConfig()->getEntityTypeId());
    }
    return $this->tempStore;
  }

  /**
   * Saves the rule configuration to the temporary storage.
   */
  protected function saveToTempStore() {
    $this->getTempStore()->set($this->getRuleConfig()->id(), $this->getRuleConfig());
  }

  /**
   * Determines if the rule coniguration is locked for the current user.
   *
   * @return bool
   *   TRUE if locked, FALSE otherwise.
   */
  protected function isLocked() {
    // If there is an object in the temporary storage from another user then
    // this configuration is locked.
    if ($this->getTempStore()->get($this->getRuleConfig()->id())
      && !$this->getTempStore()->getIfOwner($this->getRuleConfig()->id())
    ) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Provides information which user at which time locked the rule for editing.
   *
   * @return object
   *   StdClass object as provided by \Drupal\user\SharedTempStore.
   */
  protected function getLockMetaData() {
    return $this->getTempStore()->getMetadata($this->getRuleConfig()->id());
  }

  /**
   * Checks if the rule has been modified and is present in the storage.
   *
   * @return bool
   *   TRUE if the rule has been modified, FALSE otherwise.
   */
  protected function isEdited() {
    if ($this->getTempStore()->get($this->getRuleConfig()->id())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Removed the current rule configuration from the temporary storage.
   */
  protected function deleteFromTempStore() {
    $this->getTempStore()->delete($this->getRuleConfig()->id());
  }

  /**
   * Provides the config entity object that is dealt with in the temp store.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
   *   The rules config entity.
   */
  protected function getRuleConfig() {
    return $this->ruleConfig;
  }

  /**
   * Adds a message to the form if the rule configuration is locked/modified.
   *
   * @param array $form
   *   The form render array.
   */
  protected function addLockInformation(array &$form) {
    if ($this->isLocked()) {
      $form['locked'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['rules-locked', 'messages', 'messages--warning'],
        ],
        '#children' => $this->lockInformationMessage(),
        '#weight' => -10,
      ];
    }
    else {
      $form['changed'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['rules-changed', 'messages', 'messages--warning'],
        ],
        '#children' => $this->t('You have unsaved changes.'),
        '#weight' => -10,
      ];
      if (!$this->isEdited()) {
        $form['changed']['#attributes']['class'][] = 'js-hide';
      }
    }
  }

  /**
   * Validation callback that prevents editing locked rule configs.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->isLocked()) {
      $form_state->setError($form, $this->lockInformationMessage());
    }
  }

  /**
   * Provides a lock info message.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The message suitable to be shown in the UI.
   */
  protected function lockInformationMessage() {
    $lock = $this->getLockMetaData();
    $username = [
      '#theme' => 'username',
      '#account' => $this->getEntityTypeManager()->getStorage('user')->load($lock->owner),
    ];
    $lock_message_substitutions = [
      '@user' => drupal_render($username),
      '@age' => $this->getDateFormatter()->formatTimeDiffSince($lock->updated),
      ':url' => Url::fromRoute('entity.rules_reaction_rule.break_lock_form', [
        'rules_reaction_rule' => $this->getRuleConfig()->id(),
      ])->toString(),
    ];
    return $this->t('This rule is being edited by user @user, and is therefore locked from editing by others. This lock is @age old. Click here to <a href=":url">break this lock</a>.', $lock_message_substitutions);
  }

}
