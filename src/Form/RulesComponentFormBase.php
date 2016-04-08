<?php

namespace Drupal\rules\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base form for rules add and edit forms.
 */
abstract class RulesComponentFormBase extends EntityForm {

  /**
   * The Rules expression manager to get expression plugins.
   *
   * @var \Drupal\rules\Engine\ExpressionManagerInterface
   */
  protected $expressionManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.rules_expression'));
  }

  /**
   * Creates a new object of this class.
   *
   * @param \Drupal\rules\Engine\ExpressionManagerInterface $expression_manager
   *   The expression manager.
   */
  public function __construct(ExpressionManagerInterface $expression_manager) {
    $this->expressionManager = $expression_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
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
    $form['settings']['tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tags'),
      '#default_value' => implode(', ', $this->entity->getTags()),
      '#description' => $this->t('Enter a list of comma-separated tags here; e.g., "notification, publishing".'),
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
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $entity = parent::buildEntity($form, $form_state);
    $tags = array_map('trim', explode(',', $entity->get('tags')));
    $entity->set('tags', $tags);
    return $entity;
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
