<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\AutoSaveTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

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
        'entity' => ContextDefinition::create('entity')->toArray(),
      ],
    ]);
    // Just leverage the entity save action, which by default uses auto-saving.
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $entity = $this->prophesizeEntity(EntityInterface::class);
    $entity->save()->shouldBeCalledTimes(1);

    $rule->setContextValue('entity', $entity->reveal());
    $rule->execute();
  }

}
