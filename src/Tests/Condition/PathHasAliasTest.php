<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\PathHasAliasTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Path;
use Drupal\Core\Database\Database;
use Drupal\system\Tests\Path\PathUnitTestBase;

/**
 * Tests the 'Path has alias' condition.
 */
class PathHasAliasTest extends PathUnitTestBase {

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
      'name' => 'Path has alias condition test',
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
   * Tests evaluating the condition when the language context is not set.
   */
  public function testConditionEvaluationLanguageNeutral() {
    // Tests that the 'user' path has no alias.
    $condition = $this->conditionManager->createInstance('rules_path_has_alias')
      ->setContextValue('path', 'user');
    $this->assertFalse($condition->execute());

    // Create a language neutral alias.
    $this->aliasStorage->save('user', 'alias');

    // Tests that the 'user' path has an alias after creating one.
    $condition = $this->conditionManager->createInstance('rules_path_has_alias')
      ->setContextValue('path', 'user');
    $this->assertTrue($condition->execute());

    // Tests the language fallback for aliases.
    $condition = $this->conditionManager->createInstance('rules_path_has_alias')
      ->setContextValue('path', 'user')
      ->setContextValue('language', 'en');
    $this->assertTrue($condition->execute());
  }

  /**
   * Tests evaluating the condition when the language context is set.
   */
  public function testConditionEvaluationLanguageSpecific() {
    // Tests that the 'user' path has no alias for english.
    $condition = $this->conditionManager->createInstance('rules_path_has_alias')
      ->setContextValue('path', 'user')
      ->setContextValue('language', 'en');
    $this->assertFalse($condition->execute());

    // Create a 'user' path alias for english.
    $this->aliasStorage->save('user', 'alias', 'en');

    // Tests that the 'user' path has an alias for english after creating one.
    $condition = $this->conditionManager->createInstance('rules_path_has_alias')
      ->setContextValue('path', 'user')
      ->setContextValue('language', 'en');
    $this->assertTrue($condition->execute());
  }

}
