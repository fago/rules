<?php

namespace Drupal\Tests\rules\Kernel;

/**
 * Tests default config.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
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
  public static $modules = ['rules', 'rules_test',
    'rules_test_default_component', 'user', 'system',
  ];

  /**
   * Disable strict config schema checking for now.
   *
   * @var bool
   */
  protected $strictConfigSchema = TRUE;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->storage = $this->entityTypeManager->getStorage('rules_component');
    $this->installConfig(['rules_test_default_component']);
  }

  /**
   * Tests Rules default components.
   */
  public function testDefaultComponents() {
    $config_entity = $this->storage->load('rules_test_default_component');

    $user = $this->entityTypeManager->getStorage('user')
      ->create(['mail' => 'test@example.com']);

    $result = $config_entity
      ->getComponent()
      ->setContextValue('user', $user)
      ->execute();

    // Test that the action was executed correctly.
    $messages = drupal_get_messages();
    $message_string = isset($messages['status'][0]) ? (string) $messages['status'][0] : NULL;
    $this->assertEquals($message_string, 'test@example.com');

    $this->assertEquals('test@example.comtest@example.com', $result['concatenated']);
  }

}
