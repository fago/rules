<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserHasRole
 * @group RulesCondition
 */
class UserHasRoleTest extends RulesEntityIntegrationTestBase {

  /**
   * The condition that is being tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->enableModule('user');
    $this->condition = $this->conditionManager->createInstance('rules_user_has_role');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    // Set-up a mock object with roles 'authenticated' and 'editor', but not
    // 'administrator'.
    $account = $this->prophesizeEntity(UserInterface::class);
    $account->getRoles()->willReturn(['authenticated', 'editor'])
      ->shouldBeCalledTimes(7);

    $this->condition->setContextValue('user', $account->reveal());

    $authenticated = $this->prophesize(RoleInterface::class);
    $authenticated->id()->willReturn('authenticated');
    $editor = $this->prophesize(RoleInterface::class);
    $editor->id()->willReturn('editor');
    $administrator = $this->prophesize(RoleInterface::class);
    $administrator->id()->willReturn('administrator');

    // First test the default AND condition with both roles the user has.
    $this->condition->setContextValue('roles', [$authenticated->reveal(), $editor->reveal()]);
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role, this should fail.
    $this->condition->setContextValue('roles', [$authenticated->reveal(), $administrator->reveal()]);
    $this->assertFalse($this->condition->evaluate());

    // Only one role, should succeed.
    $this->condition->setContextValue('roles', [$authenticated->reveal()]);
    $this->assertTrue($this->condition->evaluate());

    // A role the user doesn't have.
    $this->condition->setContextValue('roles', [$administrator->reveal()]);
    $this->assertFalse($this->condition->evaluate());

    // Only one role, the user has with OR condition, should succeed.
    $this->condition->setContextValue('roles', [$authenticated->reveal()]);
    $this->condition->setContextValue('operation', 'OR');
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role, but has the authenticated,
    // should succeed.
    $this->condition->setContextValue('roles', [$authenticated->reveal(), $administrator->reveal()]);
    $this->condition->setContextValue('operation', 'OR');
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role. This should fail.
    $this->condition->setContextValue('roles', [$administrator->reveal()]);
    $this->condition->setContextValue('operation', 'OR');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Test the behavior with unsupported operations.
   *
   * @covers ::execute
   */
  public function testInvalidOperationException() {
    // Set the expected exception class and message.
    $this->setExpectedException('\Drupal\rules\Exception\InvalidArgumentException', 'Either use "AND" or "OR". Leave empty for default "AND" behavior.');

    // Set-up a mock object with roles 'authenticated' and 'editor', but not
    // 'administrator'.
    $account = $this->prophesizeEntity(UserInterface::class);
    $account->getRoles()->willReturn(['authenticated', 'editor']);

    $this->condition->setContextValue('user', $account->reveal());

    $authenticated = $this->prophesize(RoleInterface::class);
    $authenticated->id()->willReturn('authenticated');

    // Now test INVALID as operation. An exception must be thrown.
    $this->condition->setContextValue('roles', [$authenticated->reveal()]);
    $this->condition->setContextValue('operation', 'INVALID');
    $this->condition->evaluate();
  }

}
