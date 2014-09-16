<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\Action\AliasDeleteTest.
 */

namespace Drupal\Tests\rules\Unit\Action;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Action\AliasDelete;
use Drupal\Tests\rules\Unit\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\AliasDelete
 * @group rules_action
 */
class AliasDeleteTest extends RulesUnitTestBase {

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

    $this->action = new AliasDelete([], '', ['context' => [
      'alias' => new ContextDefinition('string')
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
    $this->assertEquals('Delete any path alias', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute()
   */
  public function testActionExecution() {

    $alias = 'about/team';

    $this->aliasStorage->expects($this->once())
      ->method('delete')
      ->with(['alias' => $alias]);

    $this->action
      ->setContextValue('alias', $this->getMockTypedData($alias));

    $this->action->execute();
  }
}
