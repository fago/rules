<?php

/**
 * @file
 * Contains \Drupal\rules_test\Plugin\Condition\TestTextCondition.
 */

namespace Drupal\rules_test\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Returns TRUE if the test parameter equals 'test value'.
 *
 * @Condition(
 *   id = "rules_test_string_condition",
 *   label = @Translation("Test condition using a string")
 * )
 */
class TestTextCondition extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['text'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Text to compare'));

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $text = $this->getContextValue('text');
    return $text == 'test value';
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // We don't care about summaries for test condition plugins.
    return '';
  }

}
