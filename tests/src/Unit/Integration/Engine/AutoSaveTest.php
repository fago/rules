<?php

namespace Drupal\Tests\rules\Unit\Integration\Engine;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * Test auto saving of variables after Rules execution.
 *
 * @group Rules
 */
class AutoSaveTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests auto saving after an action execution.
   */
  public function testActionAutoSave() {
    $rule = $this->rulesExpressionManager->createRule();
    // Just leverage the entity save action, which by default uses auto-saving.
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $entity = $this->prophesizeEntity(EntityInterface::class);
    $entity->save()->shouldBeCalledTimes(1);

    RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->setContextValue('entity', $entity->reveal())
      ->execute();
  }

}
