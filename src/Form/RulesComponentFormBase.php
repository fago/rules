<?php

/**
 * @file
 * Contains \Drupal\rules\Form\RulesComponentFormBase.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the base form for rules add and edit forms.
 */
abstract class RulesComponentFormBase extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Specify the wrapper div used by #ajax.
    $form['#prefix'] = '<div id="rules-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open' => $this->entity->isNew(),
    ];

    $form['settings']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['settings']['id'] = [
      '#type' => 'machine_name',
      '#description' => $this->t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'source' => ['settings', 'label'],
        'error' => $this->t('The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".'),
      ],
    ];

    // @todo enter a real tag field here.
    $form['settings']['tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag'),
      '#default_value' => $this->entity->getTag(),
      '#description' => $this->t('Enter a tag here'),
      '#required' => FALSE,
    ];

    $form['settings']['description'] = [
      '#type' => 'textarea',
      '#default_value' => $this->entity->getDescription(),
      '#description' => $this->t('Enter a description for this component.'),
      '#title' => $this->t('Description'),
    ];

    return parent::form($form, $form_state);
  }

  /**
   * Machine name exists callback.
   *
   * @param string $id
   *   The machine name ID.
   *
   * @return bool
   *   TRUE if an entity with the same name already exists, FALSE otherwise.
   */
  public function exists($id) {
    $type = $this->entity->getEntityTypeId();
    return (bool) $this->entityTypeManager->getStorage($type)->load($id);
  }

  /**
   * Get default form #ajax properties.
   *
   * @param string $effect
   *   (optional) The jQuery effect to use when placing the new HTML (used with
   *   'wrapper'). Valid options are 'none' (default), 'slide', or 'fade'.
   *
   * @return array
   */
  public function getDefaultAjax($effect = 'none') {
    return array(
      'callback' => '::reloadForm',
      'wrapper' => 'rules-form-wrapper',
      'effect' => $effect,
      'speed' => 'fast',
    );
  }

  /**
   * Ajax callback to reload the form.
   *
   * @return array
   *   The reloaded form.
   */
  public function reloadForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
