<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Plugin\Action\DrupalMessageTest.
 */

namespace Drupal\rules\Tests\Action;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests the 'Show message on the site' action.
 */
class DrupalMessageTest extends EntityUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['rules'];

  /**
   * The action manager.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Show message action tests',
      'description' => 'Tests the show message on the site action.',
      'group' => 'Rules',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->actionManager = $this->container->get('plugin.manager.action', $this->container->get('container.namespaces'));
  }

  /**
   * Tests the action execution.
   */
  public function testActionExecution() {
    $action = $this->actionManager->createInstance('drupal_message')
      ->setContextValue('message', 'test message')
      ->setContextValue('type', 'status')
      ->setContextValue('repeat', FALSE);

    $action->execute();
    $this->assertEqual($_SESSION['messages']['status'][0], 'test message');
  }

  /**
   * Make sure that the action works if the optional repeat flag is not set.
   */
  public function testOptionalRepeat() {
    $action = $this->actionManager->createInstance('drupal_message')
      ->setContextValue('message', 'test message')
      ->setContextValue('type', 'status');

    $action->execute();
    $this->assertEqual($_SESSION['messages']['status'][0], 'test message');
  }

}
