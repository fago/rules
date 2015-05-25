<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\RulesComponentFormBase.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base form for rules add and edit forms.
 */
abstract class RulesComponentFormBase extends EntityForm {

  /**
   * The rules component entity.
   *
   * @var \Drupal\rules\Entity\RulesComponent
   */
  protected $entity;

  /**
   * The RulesComponent storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage = NULL;

  /**
   * Constructs a new ActionAddForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The rules_component storage.
   */
  public function __construct(EntityStorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('rules_component')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form($form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    // @todo enter a real tag field here.
    $form['tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag'),
      '#default_value' => $this->entity->getTag(),
      '#description' => t('Enter a tag here'),
      '#required' => TRUE,
    ];


    $form['id'] = [
      '#type' => 'machine_name',
      '#description' => t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' =>'([^a-z0-9_]+)|(^custom$)',
        'error' => $this->t('The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".'),
      ],
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#default_value' => $this->entity->getDescription(),
      '#description' => t('Enter a description for this component.'),
      '#title' => t('Description'),
    ];

    return parent::form($form, $form_state);
  }
}
