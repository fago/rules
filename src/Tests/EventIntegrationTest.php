<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\EventIntegrationTest.
 */

namespace Drupal\rules\Tests;

/**
 * Test for the Symfony event mapping to Rules events.
 *
 * @group rules
 */
class EventIntegrationTest extends RulesDrupalTestBase {

  /**
   * The entity storage for Rules config entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->storage = $this->container->get('entity.manager')->getStorage('rules_reaction_rule');
  }

  /**
   * Test that the user login hook triggers the Rules event listener.
   */
  public function testUserLoginEvent() {
    $rule = $this->expressionManager->createInstance('rules_reaction_rule', ['event' => 'rules_user_login']);
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'expression_id' => 'rules_reaction_rule',
      'event' => 'rules_user_login',
      'configuration' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // Rebuild the container so that the newly configured event gets picked up.
    $this->kernel->rebuildContainer();
    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    $account = $this->container->get('current_user');
    // Invoke the hook manually which should trigger the rule.
    rules_user_login($account);

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('action called');
  }

}
