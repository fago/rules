<?php

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rules\Ui\RulesUiHandlerTrait;
use Drupal\rules\Engine\ActionExpressionContainerInterface;

/**
 * Form handler for action containers.
 */
class ActionContainerForm implements ExpressionFormInterface {

  use StringTranslationTrait;
  use RulesUiHandlerTrait;
  use ExpressionFormTrait;

  /**
   * The rule expression object this form is for.
   *
   * @var \Drupal\rules\Engine\ActionExpressionContainerInterface
   */
  protected $actionSet;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ActionExpressionContainerInterface $action_set) {
    $this->actionSet = $action_set;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['action_table'] = [
      '#type' => 'container',
    ];

    $form['action_table']['table'] = [
      '#theme' => 'table',
      '#caption' => $this->t('Actions'),
      '#header' => [$this->t('Elements'), $this->t('Operations')],
      '#empty' => t('None'),
    ];

    foreach ($this->actionSet as $action) {
      $form['action_table']['table']['#rows'][] = [
        'element' => $action->getLabel(),
        'operations' => [
          'data' => [
            '#type' => 'dropbutton',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => $this->getRulesUiHandler()->getUrlFromRoute('expression.edit', [
                  'uuid' => $action->getUuid(),
                ]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => $this->getRulesUiHandler()->getUrlFromRoute('expression.delete', [
                  'uuid' => $action->getUuid(),
                ]),
              ],
            ],
          ],
        ],
      ];
    }

    // @todo Put this into the table as last row and style it like it was in
    // Drupal 7 Rules.
    $form['add_action'] = [
      '#attributes' => ['class' => ['action-links']],
      '#theme' => 'menu_local_action',
      '#link' => [
        'title' => $this->t('Add action'),
        'url' => $this->getRulesUiHandler()->getUrlFromRoute('expression.add', [
          'expression_id' => 'rules_action',
        ]),
      ],
    ];

    return $form;
  }

}
