<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\PathAliasDeleteByPath
 * @group RulesAction
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
   * @var \Drupal\Core\Path\AliasStorageInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->aliasStorage = $this->prophesize(AliasStorageInterface::class);

    $this->container->set('path.alias_storage', $this->aliasStorage->reveal());

    $this->action = $this->actionManager->createInstance('rules_path_alias_delete_by_path');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Delete all aliases for a path', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {

    $path = 'node/1';

    $this->aliasStorage->delete(['path' => $path])->shouldBeCalledTimes(1);

    $this->action
      ->setContextValue('path', $path);

    $this->action->execute();
  }

}
