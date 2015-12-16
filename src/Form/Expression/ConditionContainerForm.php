<?php

/**
 * @file
 * Contains \Drupal\rules\Form\Expression\ConditionContainerForm.
 */

namespace Drupal\rules\Form\Expression;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\rules\Engine\ConditionExpressionContainerInterface;

/**
 * Form view structure for Rules condition containers.
 */
class ConditionContainerForm implements ExpressionFormInterface {

  use StringTranslationTrait;

  /**
   * The rule expression object this form is for.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionContainerInterface
   */
  protected $conditionContainer;

  /**
   * Creates a new object of this class.
   */
  public function __construct(ConditionExpressionContainerInterface $condition_container) {
    $this->conditionContainer = $condition_container;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['conditions'] = [
      '#type' => 'container',
    ];

    $form['conditions']['table'] = [
      '#theme' => 'table',
      '#caption' => $this->t('Conditions'),
      '#header' => [$this->t('Elements'), $this->t('Operations')],
      '#empty' => t('None'),
    ];

    foreach ($this->conditionContainer as $uuid => $condition) {
      $form['conditions']['table']['#rows'][] = [
        'element' => $condition->getLabel(),
        'operations' => [
          'data' => [
            '#type' => 'dropbutton',
            '#links' => [
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('rules.reaction_rule.expression.delete', [
                  'rules_reaction_rule' => $this->conditionContainer->getRoot()->getConfigEntityId(),
                  'uuid' => $uuid,
                ]),
              ],
            ],
          ],
        ],
      ];
    }

    // @todo Put this into the table as last row and style it like it was in
    // Drupal 7 Rules.
    $form['add_condition'] = [
      '#theme' => 'menu_local_action',
      '#link' => [
        'title' => $this->t('Add condition'),
        'url' => Url::fromRoute('rules.reaction_rule.condition.add', [
          'reaction_config' => $this->conditionContainer->getRoot()->getConfigEntityId(),
        ]),
      ],
    ];

    return $form;
  }

}
