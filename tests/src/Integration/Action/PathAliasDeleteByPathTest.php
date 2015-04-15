<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\PathAliasDeleteByPathTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\PathAliasDeleteByPath
 * @group rules_action
 */
class PathAliasDeleteByPathTest extends RulesIntegrationTestBase {

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

    $this->aliasStorage = $this->getMock('Drupal\Core\Path\AliasStorageInterface');
    $this->container->set('path.alias_storage', $this->aliasStorage);

    $this->action = $this->actionManager->createInstance('rules_path_alias_delete_by_path');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Delete alias for a path', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {

    $path = 'node/1';

    $this->aliasStorage->expects($this->once())
      ->method('delete')
      ->with(['path' => $path]);

    $this->action
      ->setContextValue('path', $path);

    $this->action->execute();
  }
}
