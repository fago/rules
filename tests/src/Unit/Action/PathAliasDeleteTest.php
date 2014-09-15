<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Action\PathAliasDeleteTest.
 */

namespace Drupal\Tests\rules\Unit\Action;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Action\PathAliasDelete;
use Drupal\Tests\rules\Unit\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\PathAliasDelete
 * @group rules_action
 */
class PathAliasDeleteTest extends RulesUnitTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
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

    $this->action = new PathAliasDelete([], '', ['context' => [
      'path' => new ContextDefinition('string')
    ]], $this->aliasStorage);

    $this->action->setStringTranslation($this->getMockStringTranslation());
    $this->action->setTypedDataManager($this->getMockTypedDataManager());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Delete alias for a path', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute()
   */
  public function testActionExecution() {

    $path = 'node/1';

    $this->aliasStorage->expects($this->once())
      ->method('delete')
      ->with(['path' => $path]);

    $this->action
      ->setContextValue('path', $this->getMockTypedData($path));

    $this->action->execute();
  }
}
