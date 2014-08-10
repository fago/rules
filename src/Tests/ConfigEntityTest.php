<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ConfigEntityTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests storage and loading of Rules config entities.
 *
 * @group rules
 */
class ConfigEntityTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests that an empty rule configuration can be saved.
   */
  public function testSavingEmptyRule() {
    $entity_manager = $this->container->get('entity.manager');
    $config_entity = $entity_manager->getStorage('rule')
      ->create(['id' => 'test_rule']);
    $config_entity->save();
  }

}
