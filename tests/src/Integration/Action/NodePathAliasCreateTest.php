<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\NodePathAliasCreateTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\NodePathAliasCreate
 * @group rules_actions
 */
class NodePathAliasCreateTest extends RulesEntityIntegrationTestBase {

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
    $this->enableModule('node');

    $this->aliasStorage = $this->getMock('Drupal\Core\Path\AliasStorageInterface');
    $this->container->set('path.alias_storage', $this->aliasStorage);
    $this->action = $this->actionManager->createInstance('rules_node_path_alias_create');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Create node path alias', $this->action->summary());
  }

  /**
   * Tests the action execution with an unsaved node.
   *
   * @covers ::execute()
   */
  public function testActionExecutionWithUnsavedNode() {
    $node = $this->getMockNode();
    $node->expects($this->once())
      ->method('isNew')
      ->will($this->returnValue(TRUE));

    // Test that new nodes are saved first.
    $node->expects($this->once())
      ->method('save');

    $this->action->setContextValue('node', $node)
      ->setContextValue('alias', 'about');

    $this->action->execute();
  }

  /**
   * Tests the action execution with a saved node.
   *
   * @covers ::execute()
   */
  public function testActionExecutionWithSavedNode() {
    $node = $this->getMockNode();
    $node->expects($this->once())
      ->method('isNew')
      ->will($this->returnValue(FALSE));

    // Test that existing nodes are not saved again.
    $node->expects($this->never())
      ->method('save');

    $this->action->setContextValue('node', $node)
      ->setContextValue('alias', 'about');

    $this->action->execute();
  }

  /**
   * Creates a mock node.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\node\NodeInterface
   *   The mocked node object.
   */
  protected function getMockNode() {
    $language = $this->getMock('Drupal\Core\language\LanguageInterface');
    $language->expects($this->once())
      ->method('getId')
      ->will($this->returnValue('en'));

    $node = $this->getMock('Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('language')
      ->will($this->returnValue($language));

    $url = $this->getMockBuilder('Drupal\Core\Url')
      ->disableOriginalConstructor()
      ->getMock();

    $url->expects($this->once())
      ->method('getInternalPath')
      ->will($this->returnValue('node/1'));

    $node->expects($this->once())
      ->method('urlInfo')
      ->with('canonical')
      ->will($this->returnValue($url));

    $this->aliasStorage->expects($this->once())
      ->method('save')
      ->with('node/1', 'about', 'en');

    return $node;
  }

}
