<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Context\ContextConfig;

/**
 * Tests that action specfic config schema works.
 *
 * @group rules
 */
class ConfigSchemaTest extends RulesDrupalTestBase {

  /**
   * The entity storage for Rules config entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->storage = $this->container->get('entity_type.manager')->getStorage('rules_component');
  }

  /**
   * Make sure the system send email config schema works on saving.
   */
  public function testMailActionContextSchema() {
    $rule = $this->expressionManager
      ->createRule();
    $rule->addAction('rules_send_email', ContextConfig::create()
      ->setValue('to', ['test@exmaple.com'])
      ->setValue('message', 'mail body')
      ->setValue('subject', 'test subject')
    );

    $config_entity = $this->storage->create([
      'id' => 'test_rule',
    ])->setExpression($rule);
    $config_entity->save();
  }

}
