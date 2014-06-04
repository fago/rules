<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\PathAliasExistsTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Path;
use Drupal\Core\Database\Database;
use Drupal\system\Tests\Path\PathUnitTestBase;

/**
 * Tests the 'Path alias exists' condition.
 */
class PathAliasExistsTest extends PathUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'rules', 'path'];

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The alias manager.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface;
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Path alias exists condition test',
      'description' => 'Tests the condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->conditionManager = $this->container->get('plugin.manager.condition', $this->container->get('container.namespaces'));
    $this->aliasStorage = $this->container->get('path.alias_storage');
    $this->fixtures->createTables(Database::getConnection());
  }

  /**
   * Tests evaluating the condition.
   */
  public function testConditionEvaluation() {
    $this->aliasStorage->save('original', 'language-neutral-alias');
    $this->aliasStorage->save('original', 'english-alias', 'en');

    // Test an alias that does not exist.
    $condition = $this->conditionManager->createInstance('rules_path_alias_exists')
      ->setContextValue('alias', 'does-not-exist');
    $this->assertFalse($condition->execute());

    // Test an alias that exists.
    $condition = $this->conditionManager->createInstance('rules_path_alias_exists')
      ->setContextValue('alias', 'language-neutral-alias');
    $this->assertTrue($condition->execute());

    // Test a language specific alias that does not exist.
    $condition = $this->conditionManager->createInstance('rules_path_alias_exists')
      ->setContextValue('alias', 'does-not-exist')
      ->setContextValue('language', 'en');
    $this->assertFalse($condition->execute());

    // Test a language specific alias that does not exist.
    $condition = $this->conditionManager->createInstance('rules_path_alias_exists')
      ->setContextValue('alias', 'english-alias')
      ->setContextValue('language', 'en');
    $this->assertTrue($condition->execute());
  }

}
