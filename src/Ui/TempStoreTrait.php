<?php

namespace Drupal\rules\Ui;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\SharedTempStoreFactory;

/**
 * Provides methods for modified rules components in temporary storage.
 *
 * Note that this implements the lock-related methods of
 * \Drupal\rules\Ui\RulesUiHandlerInterface.
 *
 * @see \Drupal\rules\Ui\RulesUiHandlerInterface
 * @see \Drupal\rules\Ui\RulesUiConfigHandler
 */
trait TempStoreTrait {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The temporary store for the rules component.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The currently active rules UI handler.
   *
   * @var \Drupal\rules\Ui\RulesUiHandlerInterface
   */
  protected $rulesUiHandler;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Retrieves the renderer service if not already present.
   *
   * @return \Drupal\Core\Render\RendererInterface
   *   The renderer service.
   */
  public function getRenderer() {
    if (!isset($this->renderer)) {
      $this->renderer = \Drupal::service('renderer');
    }
    return $this->renderer;
  }

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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
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
   * Gets the currently active RulesUI's handler.
   *
   * @return \Drupal\rules\Ui\RulesUiHandlerInterface
   *   The RulesUI handler.
   */
  protected function getRulesUiHandler() {
    // Usually the trait is used on the RulesUI handler.
    return $this;
  }

  /**
   * Fetches the stored data from the temporary storage.
   *
   * @return mixed|null
   *   The stored data or NULL if the temp store is empty.
   */
  protected function fetchFromTempStore() {
    return $this->getTempStore()->get($this->getTempStoreItemId());
  }

  /**
   * Stores some data in the temporary storage.
   *
   * @param mixed $data
   *   The data to store.
   */
  protected function storeToTempStore($data) {
    $this->getTempStore()->set($this->getTempStoreItemId(), $data);
  }

  /**
   * @see \Drupal\rules\Ui\RulesUiHandlerInterface::clearTemporaryStorage()
   */
  public function clearTemporaryStorage() {
    $this->getTempStore()->delete($this->getTempStoreItemId());
  }

  /**
   * @see \Drupal\rules\Ui\RulesUiHandlerInterface::isLocked()
   */
  public function isLocked() {
    // If there is an object in the temporary storage from another user then
    // this component is locked.
    if ($this->getTempStore()->get($this->getTempStoreItemId()) && !$this->getTempStore()->getIfOwner($this->getTempStoreItemId())
    ) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Generates the temp store item's ID to use for the edited component.
   *
   * @return string
   *   The temp store ID.
   */
  private function getTempStoreItemId() {
    // The internal path is unique for the currently edited component.
    return $this->getRulesUiHandler()->getBaseRouteUrl()->getInternalPath();
  }

  /**
   * Gets the temporary storage repository from the factory.
   *
   * @return \Drupal\user\SharedTempStore
   *   The shareds storage.
   */
  private function getTempStore() {
    if (!isset($this->tempStore)) {
      $this->tempStore = $this->getTempStoreFactory()->get($this->getRulesUiHandler()->getPluginId());
    }
    return $this->tempStore;
  }

  /**
   * @see \Drupal\rules\Ui\RulesUiHandlerInterface::getLockMetaData()
   */
  public function getLockMetaData() {
    return $this->getTempStore()->getMetadata($this->getTempStoreItemId());
  }

  /**
   * @see \Drupal\rules\Ui\RulesUiHandlerInterface::isEdited()
   */
  public function isEdited() {
    if ($this->getTempStore()->get($this->getTempStoreItemId())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @see \Drupal\rules\Ui\RulesUiHandlerInterface::addLockInformation()
   */
  public function addLockInformation() {
    $build = [];
    if ($this->isLocked()) {
      $build['locked'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['rules-locked', 'messages', 'messages--warning'],
        ],
        '#children' => $this->lockInformationMessage(),
        '#weight' => -10,
      ];
    }
    else {
      $build['changed'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['rules-changed', 'messages', 'messages--warning'],
        ],
        '#children' => $this->t('You have unsaved changes.'),
        '#weight' => -10,
      ];
      if (!$this->isEdited()) {
        $build['changed']['#attributes']['class'][] = 'js-hide';
      }
    }
    return $build;
  }

  /**
   * @see \Drupal\rules\Ui\RulesUiHandlerInterface::validateLock()
   */
  public function validateLock(array &$form, FormStateInterface $form_state) {
    if ($this->isLocked()) {
      $form_state->setErrorByName('locked', $this->lockInformationMessage());
    }
  }

  /**
   * Provides a lock info message.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The message suitable to be shown in the UI.
   */
  private function lockInformationMessage() {
    $lock = $this->getLockMetaData();
    $username = [
      '#theme' => 'username',
      '#account' => $this->getEntityTypeManager()->getStorage('user')->load($lock->owner),
    ];
    $lock_message_substitutions = [
      '@user' => $this->getRenderer()->render($username),
      '@age' => $this->getDateFormatter()->formatTimeDiffSince($lock->updated),
      '@component_type' => $this->getRulesUiHandler()->getPluginDefinition()->component_type_label,
      ':url' => Url::fromRoute($this->getRulesUiHandler()->getPluginDefinition()->base_route . '.break_lock', \Drupal::routeMatch()->getRawParameters()->all())->toString(),
    ];
    return $this->t('This @component_type is being edited by user @user, and is therefore locked from editing by others. This lock is @age old. Click here to <a href=":url">break this lock</a>.', $lock_message_substitutions);
  }

}
