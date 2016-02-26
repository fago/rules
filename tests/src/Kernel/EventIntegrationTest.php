<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Kernel\EventIntegrationTest.
 */

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Context\ContextConfig;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\KernelEvents;

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
    $this->storage = $this->container->get('entity_type.manager')->getStorage('rules_reaction_rule');
  }

  /**
   * Test that the user login hook triggers the Rules event listener.
   */
  public function testUserLoginEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log',
      ContextConfig::create()
        ->map('message', 'account.name.0.value')
    );

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => 'rules_user_login']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    $account = User::create(['name' => 'test_user']);
    // Invoke the hook manually which should trigger the rule.
    rules_user_login($account);

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('test_user');
  }

  /**
   * Test that the user logout hook triggers the Rules event listener.
   */
  public function testUserLogoutEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => 'rules_user_logout']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    $account = $this->container->get('current_user');
    // Invoke the hook manually which should trigger the rule.
    rules_user_logout($account);

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Test that the cron hook triggers the Rules event listener.
   */
  public function testCronEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => 'rules_system_cron']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    // Run cron.
    $this->container->get('cron')->run();

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Test that a Logger message trigger the Rules logger listener.
   */
  public function testSystemLoggerEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => 'rules_system_logger_event']],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    // Creates a logger-item, that must be dispatched as event.
    $this->container->get('logger.factory')->get('rules_test')
      ->notice("This message must get logged and dispatched as rules_system_logger_event");

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Test that Drupal initializing triggers the Rules logger listener.
   */
  public function testInitEvent() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
      'events' => [['event_name' => KernelEvents::REQUEST]],
      'expression' => $rule->getConfiguration(),
    ]);
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    $dispatcher = $this->container->get('event_dispatcher');

    // Remove all the listeners except Rules before triggering an event.
    $listeners = $dispatcher->getListeners(KernelEvents::REQUEST);
    foreach ($listeners as $listener) {
      if (empty($listener[1]) || $listener[1] != 'onRulesEvent') {
        $dispatcher->removeListener(KernelEvents::REQUEST, $listener);
      }
    }
    // Manually trigger the initialization event.
    $dispatcher->dispatch(KernelEvents::REQUEST);

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('action called');
  }

  /**
   * Test that rules config supports multiple events.
   */
  public function testMultipleEvents() {
    $rule = $this->expressionManager->createRule();
    $rule->addCondition('rules_test_true');
    $rule->addAction('rules_test_log');

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
    ]);
    $config_entity->set('events', [
      ['event_name' => 'rules_user_login'],
      ['event_name' => 'rules_user_logout'],
    ]);
    $config_entity->set('expression', $rule->getConfiguration());
    $config_entity->save();

    // The logger instance has changed, refresh it.
    $this->logger = $this->container->get('logger.channel.rules');

    $account = User::create(['name' => 'test_user']);
    // Invoke the hook manually which should trigger the rules_user_login event.
    rules_user_login($account);
    // Invoke the hook manually which should trigger the rules_user_logout
    // event.
    rules_user_logout($account);

    // Test that the action in the rule logged something.
    $this->assertRulesLogEntryExists('action called');
    $this->assertRulesLogEntryExists('action called', 1);
  }

}
