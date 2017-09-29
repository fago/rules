<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\DataListItemRemove
 * @group RulesAction
 */
class DataListItemRemoveTest extends RulesIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->action = $this->actionManager->createInstance('rules_list_item_remove');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Remove item from list', $this->action->summary());
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute
   */
  public function testActionExecution() {
    $list = ['One', 'Two', 'Three'];

    $this->action
      ->setContextValue('list', $list)
      ->setContextValue('item', 'Two');

    $this->action->execute();

    // The second item should be removed from the list.
    $this->assertArrayEquals(['One', 'Three'], array_values($this->action->getContextValue('list')));
  }

}
