<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ConfigEntityDefaultsTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Engine\RulesLog;
use Drupal\rules\Entity\RulesComponent;

/**
 * Tests default config.
 *
 * @group rules
 */
class ConfigEntityDefaultsTest extends RulesDrupalTestBase {

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
  public static $modules = ['rules', 'rules_test_default_component', 'user', 'system'];

  /**
   * Disable strict config schema checking for now
   *
   * @todo: Fix once config schema has been improved.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->storage = $this->container->get('entity.manager')->getStorage('rules_component');
    $this->installConfig(['rules_test_default_component']);
  }

  /**
   * Tests Rules default components.
   */
  public function testDefaultComponents() {
    $config_entity = $this->storage->load('rules_test_default_component');

    /** @var $config_entity RulesComponent */
    $expression = $config_entity
      ->getExpression();

    $expression
      ->setContextValue('user', \Drupal::currentUser())
      ->execute();

    // @todo: For some reason this does not work yet.
    // Test that the action was executed.
    debug($_SESSION['messages']);
    debug(drupal_get_messages());
  }

}
