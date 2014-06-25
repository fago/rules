<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserIsBlockedTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\rules\Plugin\Condition\UserIsBlocked;

/**
 * Tests the 'User is blocked' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserIsBlocked
 *
 * @see \Drupal\rules\Plugin\Condition\UserIsBlocked
 */
class UserIsBlockedTest extends ConditionTestBase {

  /**
   * The condition to be tested.
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
      'name' => 'User is blocked condition test',
      'description' => 'Tests the user is blocked condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new UserIsBlocked([], '', [], $this->typedDataManager);
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
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('User is blocked', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValue() {
    $user = $this->getMock('Drupal\user\UserInterface');

    // Test setting and getting the context value.
    $this->assertSame($this->condition, $this->condition->setContextValue('user', $user));
    $this->assertSame($user, $this->condition->getContextValue('user'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $user = $this->getMock('Drupal\user\UserInterface');
    $user->expects($this->at(0))
      ->method('isBlocked')
      ->will($this->returnValue(TRUE));

    $user->expects($this->at(1))
      ->method('isBlocked')
      ->will($this->returnValue(FALSE));

    // Set the user context value.
    $this->condition->setContextValue('user', $user);

    // Test evaluation. The first invocation should return TRUE, the second
    // should return FALSE.
    $this->assertTrue($this->condition->evaluate());
    $this->assertFalse($this->condition->evaluate());
  }

}
