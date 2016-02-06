<?php

/**
 * @file
 * Contains \Drupal\rules\Form\Expression\ActionSetForm.
 */

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\rules\Engine\ActionExpressionContainerInterface;

/**
 * Form view structure for Rules action sets.
 */
class ActionSetForm implements ExpressionFormInterface {

  use StringTranslationTrait;
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
                'url' => Url::fromRoute('entity.rules_reaction_rule.edit_form.expression.edit', [
                  'rules_reaction_rule' => $this->actionSet->getRoot()->getConfigEntityId(),
                  'uuid' => $action->getUuid(),
                ]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('entity.rules_reaction_rule.edit_form.expression.delete', [
                  'rules_reaction_rule' => $this->actionSet->getRoot()->getConfigEntityId(),
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
      '#theme' => 'menu_local_action',
      '#link' => [
        'title' => $this->t('Add action'),
        'url' => Url::fromRoute('entity.rules_reaction_rule.edit_form.expression.add', [
          'rules_reaction_rule' => $this->actionSet->getRoot()->getConfigEntityId(),
          'expression_id' => 'rules_action',
        ]),
      ],
    ];

    return $form;
  }

}
