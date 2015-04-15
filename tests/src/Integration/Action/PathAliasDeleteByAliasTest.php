<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\PathAliasDeleteByAliasTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\PathAliasDeleteByAlias
 * @group rules_actions
 */
class PathAliasDeleteByAliasTest extends RulesIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * The mocked alias storage service.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->aliasStorage = $this->getMock('Drupal\Core\Path\AliasStorageInterface');
    $this->container->set('path.alias_storage', $this->aliasStorage);

    $this->action = $this->actionManager->createInstance('rules_path_alias_delete_by_alias');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Delete any path alias', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $alias = 'about/team';

    $this->aliasStorage->expects($this->once())
      ->method('delete')
      ->with(['alias' => $alias]);

    $this->action
      ->setContextValue('alias', $alias);

    $this->action->execute();
  }
}
