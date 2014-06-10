<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserHasRoleTest.
 */

namespace Drupal\rules\Tests\Condition;

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
   * The mocked user account.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\user\Entity\User
   */
  protected $account;

  /**
   * The condition that is being tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * The mocked typed data manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'User has role(s( condition test',
      'description' => 'Tests the user has role(s) condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new UserHasRole([], '', [], $this->typedDataManager);
    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests the context definitions.
   *
   * @covers ::contextDefinitions()
   */
  public function testContextDefinition() {
    // Test that the 'user' context is properly defined.
    $context = $this->condition->getContext('user');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('User', $definition->getLabel());
    $this->assertEquals('entity:user', $definition->getDataType());
    $this->assertTrue($definition->isRequired());

    // Test that the 'roles' context is properly defined.
    $context = $this->condition->getContext('roles');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Roles', $definition->getLabel());
    $this->assertEquals('user:roles', $definition->getDataType());
    $this->assertTrue($definition->isRequired());

    // Test that the 'operation' context is properly defined.
    $context = $this->condition->getContext('operation');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Match roles', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertFalse($definition->isRequired());
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
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValue() {
    // We can't mock the UserInterface because there is a bug in PHPUnit below
    // version 3.8 that causes mocking of interfaces that extend \Traversable
    // to fail. @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
    $user = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()
      ->getMock();

    // Test setting and getting the context value.
    $this->assertSame($this->condition, $this->condition->setContextValue('user', $user));
    $this->assertSame($user, $this->condition->getContextValue('user'));

    // Test setting and getting context values.
    $this->assertSame($this->condition, $this->condition->setContextValue('operation', 'OR'));
    $this->assertSame('OR', $this->condition->getContextValue('operation'));
    $this->assertSame($this->condition, $this->condition->setContextValue('roles', ['authenticated', 'editor']));
    $this->assertSame(['authenticated', 'editor'], $this->condition->getContextValue('roles'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    // Set-up a mock object with roles 'authenticated' and 'editor', but not 'administrator'.
    $this->account = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()
      ->getMock();

    $this->account->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue(['authenticated', 'editor']));

    $this->condition->setContextValue('user', $this->account);

    // First test the default AND condition with both roles the user has.
    $this->condition->setContextValue('roles', ['authenticated', 'editor']);
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role, this should fail.
    $this->condition->setContextValue('roles', ['authenticated', 'administrator']);
    $this->assertFalse($this->condition->evaluate());

    // Only one role, should succeed.
    $this->condition->setContextValue('roles', ['authenticated']);
    $this->assertTrue($this->condition->evaluate());

    // A role the user doesn't have.
    $this->condition->setContextValue('roles', ['administrator']);
    $this->assertFalse($this->condition->evaluate());

    // Only one role, the user has with OR condition, should succeed.
    $this->condition->setContextValue('roles', ['authenticated']);
    $this->condition->setContextValue('operation', 'OR');
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role, but has the authenticated, should succeed.
    $this->condition->setContextValue('roles', ['authenticated', 'administrator']);
    $this->condition->setContextValue('operation', 'OR');
    $this->assertTrue($this->condition->evaluate());

    // User doesn't have the administrator role. This should fail.
    $this->condition->setContextValue('roles', ['administrator']);
    $this->condition->setContextValue('operation', 'OR');
    $this->assertFalse($this->condition->evaluate());
  }

}
