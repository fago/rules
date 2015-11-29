<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ConfigEntityDefaultsTest.
 */

namespace Drupal\Tests\rules\Kernel;

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
   * Disable strict config schema checking for now.
   *
   * @todo: Fix once config schema has been improved.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityManager = $this->container->get('entity.manager');
    $this->storage = $this->entityManager->getStorage('rules_component');
    $this->installConfig(['rules_test_default_component']);
  }

  /**
   * Tests Rules default components.
   */
  public function testDefaultComponents() {
    $config_entity = $this->storage->load('rules_test_default_component');

    $user = $this->entityManager->getStorage('user')
      ->create(array('mail' => 'test@example.com'));

    /** @var $config_entity \Drupal\rules\Entity\RulesComponent */
    $config_entity
      ->getComponent()
      ->setContextValue('user', $user)
      ->execute();

    // Test that the action was executed correctly.
    $messages = drupal_get_messages();
    $message_string = isset($messages['status'][0]) ? (string) $messages['status'][0] : NULL;
    $this->assertEquals($message_string, 'test@example.com');
  }

}
