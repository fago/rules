<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\AutoSaveTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;
use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * Test auto saving of variables after Rules execution.
 *
 * @group rules
 */
class AutoSaveTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests auto saving after an action execution.
   */
  public function testActionAutoSave() {
    $rule = $this->rulesExpressionManager->createRule([
      'context_definitions' => [
        'entity' => [
          'type' => 'entity',
        ],
      ],
    ]);
    // Just leverage the entity save action, which by default uses auto-saving.
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('save');

    $rule->setContextValue('entity', $entity);
    $rule->execute();
  }

}
