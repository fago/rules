<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Action\CreateNodePathAliasTest.
 */

namespace Drupal\rules\Tests\Action;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Action\CreateNodePathAlias;
use Drupal\rules\Tests\RulesTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\CreateNodePathAlias
 * @group rules_actions
 */
class CreateNodePathAliasTest extends RulesTestBase {

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

    $this->action = new CreateNodePathAlias([], '', ['context' => [
      'node' => new ContextDefinition('entity:node'),
      'alias' => new ContextDefinition('string'),
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

    $this->action->setContextValue('node', $this->getMockTypedData($node))
      ->setContextValue('alias', $this->getMockTypedData('about'));

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

    $this->action->setContextValue('node', $this->getMockTypedData($node))
      ->setContextValue('alias', $this->getMockTypedData('about'));

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
