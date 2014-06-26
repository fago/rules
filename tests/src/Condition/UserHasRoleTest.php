<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserHasRoleTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\UserHasRole;

/**
 * Tests the 'User has role(s)' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserHasRole
 *
 * @see \Drupal\rules\Plugin\Condition\UserHasRole
 */
class UserHasRoleTest extends ConditionTestBase {

  /**
   * The condition that is being tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'User has role(s) condition test',
      'description' => 'Tests the user has role(s) condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = new UserHasRole([], '', ['context' => [
      'user' => new ContextDefinition('entity:user'),
      'roles' => new ContextDefinition('entity:role', NULL, TRUE, TRUE),
      'operation' => new ContextDefinition('string'),
    ]]);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('User has role(s)', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    // Set-up a mock object with roles 'authenticated' and 'editor', but not
    // 'administrator'.
    $account = $this->getMock('Drupal\user\UserInterface');
    $account->expects($this->exactly(7))
      ->method('getRoles')
      ->will($this->returnValue(['authenticated', 'editor']));

    $this->condition->setContextValue('user', $account);

    $authenticated = $this->getMockRole('authenticated');
    $editor = $this->getMockRole('editor');
    $administrator = $this->getMockRole('administrator');

    $this->condition->setContextValue('user', $this->getMockTypedData($account));

    // First test the default AND condition with both roles the user has.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$authenticated, $editor]));
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role, this should fail.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$authenticated, $administrator]));
    $this->assertFalse($this->condition->evaluate());

    // Only one role, should succeed.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$authenticated]));
    $this->assertTrue($this->condition->evaluate());

    // A role the user doesn't have.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$administrator]));
    $this->assertFalse($this->condition->evaluate());

    // Only one role, the user has with OR condition, should succeed.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$authenticated]));
    $this->condition->setContextValue('operation', $this->getMockTypedData('OR'));
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role, but has the authenticated,
    // should succeed.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$authenticated, $administrator]));
    $this->condition->setContextValue('operation', $this->getMockTypedData('OR'));
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role. This should fail.
    $this->condition->setContextValue('roles', $this->getMockTypedData([$administrator]));
    $this->condition->setContextValue('operation', $this->getMockTypedData('OR'));
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Creates a mocked user role.
   *
   * @param $id
   *   The machine-readable name of the mocked role.
   *
   * @return \PHPUnit_Framework_MockObject_MockBuilder|\Drupal\user\RoleInterface
   *   The mocked role.
   */
  protected function getMockRole($id) {
    $role = $this->getMockBuilder('Drupal\user\Entity\Role')
      ->disableOriginalConstructor()
      ->setMethods(['id'])
      ->getMock();

    $role->expects($this->any())
      ->method('id')
      ->will($this->returnValue($id));

    return $role;
  }

}
