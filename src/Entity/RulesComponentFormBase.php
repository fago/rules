<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\RulesComponentFormBase.
 */

namespace Drupal\rules\Entity;

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
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#description' => $this->t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => $this->t('The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".'),
      ],
    ];

    // @todo enter a real tag field here.
    $form['tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag'),
      '#default_value' => $this->entity->getTag(),
      '#description' => $this->t('Enter a tag here'),
      '#required' => FALSE,
    ];

    $form['description'] = [
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

}
