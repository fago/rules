<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserHasEntityFieldAccessTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Language\Language;
use Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess;

/**
 * Tests the 'User has entity field access' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess
 *
 * @see \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess
 */
class UserHasEntityFieldAccessTest extends ConditionTestBase {

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
   * The mocked entity access handler.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Entity\EntityAccessControllerInterface
   */
  protected $entityAccess;

  /**
   * The mocked entity manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Entity is of bundle condition test',
      'description' => 'Tests whether an entity is of a particular [type and] bundle.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityAccess = $this->getMock('Drupal\Core\Entity\EntityAccessControllerInterface');
    $this->entityManager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->any())
      ->method('getAccessController')
      ->with($this->anything())
      ->will($this->returnValue($this->entityAccess));

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->condition = new UserHasEntityFieldAccess([], '', [], $this->typedDataManager, $this->entityManager);
    $this->condition->setStringTranslation($this->getMockStringTranslation());
  }

  /**
   * Tests that the dependencies are properly set in the constructor.
   *
   * @covers ::__construct()
   */
  public function testConstructor() {
    $this->assertSame($this->typedDataManager, $this->condition->getTypedDataManager());
  }

  /**
   * Tests the context definitions.
   *
   * @covers ::contextDefinitions()
   */
  public function testContextDefinition() {
    // Test that the 'entity' context is properly defined.
    $context = $this->condition->getContext('entity');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Entity', $definition->getLabel());
    $this->assertEquals('entity', $definition->getDataType());
    $this->assertTrue($definition->isRequired());

    // Test that the 'field' context is properly defined.
    $context = $this->condition->getContext('field');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Field name', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertTrue($definition->isRequired());

    // Test that the 'op' context is properly defined.
    $context = $this->condition->getContext('op');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $context);
    $definition = $context->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Operation', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertTrue($definition->isRequired());

    // Test that the 'account' context is properly defined.
    $context = $this->condition->getContext('account');
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
    $this->assertEquals('User has access to field on entity', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValues() {
    // Test setting and getting context values.
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $this->assertSame($this->condition, $this->condition->setContextValue('entity', $entity));
    $this->assertSame($entity, $this->condition->getContextValue('entity'));

    $this->assertSame($this->condition, $this->condition->setContextValue('field', 'my-field'));
    $this->assertSame('my-field', $this->condition->getContextValue('field'));

    $this->assertSame($this->condition, $this->condition->setContextValue('op', 'edit'));
    $this->assertSame('edit', $this->condition->getContextValue('op'));

    // We can't mock the ContentEntityInterface because there is a bug in PHPUnit below
    // version 3.8 that causes mocking of interfaces that extend \Traversable
    // to fail. @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
    $user = $this->getMockBuilder('Drupal\user\Entity\User')->disableOriginalConstructor()->getMock();
    $this->assertSame($this->condition, $this->condition->setContextValue('account', $user));
    $this->assertSame($user, $this->condition->getContextValue('account'));
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    // We can't mock the interfaces of these classes because there is a bug in
    // PHPUnit below version 3.8 that causes mocking of interfaces that extend
    // \Traversable to fail. @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
    $account = $this->getMockBuilder('Drupal\user\Entity\User')->disableOriginalConstructor()->getMock();
    $entity = $this->getMocKBuilder('Drupal\Core\Entity\ContentEntityBase')->disableOriginalConstructor()->getMock();
    $items = $this->getMocKBuilder('Drupal\Core\Field\FieldItemList')->disableOriginalConstructor()->getMock();

    $entity->expects($this->exactly(3))
      ->method('hasField')
      ->with('potato-field')
      ->will($this->returnValue(TRUE));

    $definition = $this->getMock('Drupal\Core\Field\FieldDefinitionInterface');
    $entity->expects($this->exactly(2))
      ->method('getFieldDefinition')
      ->with('potato-field')
      ->will($this->returnValue($definition));

    $entity->expects($this->exactly(2))
      ->method('get')
      ->with('potato-field')
      ->will($this->returnValue($items));

    $this->condition->setContextValue('entity', $entity)
      ->setContextValue('field', 'potato-field')
      ->setContextValue('account', $account);

    $this->entityAccess->expects($this->exactly(3))
      ->method('access')
      ->will($this->returnValueMap([
        [$entity, 'view', Language::LANGCODE_DEFAULT, $account, TRUE],
        [$entity, 'edit', Language::LANGCODE_DEFAULT, $account, TRUE],
        [$entity, 'delete', Language::LANGCODE_DEFAULT, $account, FALSE],
      ]));

    $this->entityAccess->expects($this->exactly(2))
      ->method('fieldAccess')
      ->will($this->returnValueMap([
        ['view', $definition, $account, $items, TRUE],
        ['edit', $definition, $account, $items, FALSE],
      ]));

    // Test with 'view', 'edit' and 'delete'. Both 'view' and 'edit' will have
    // general entity access, but the 'potato-field' should deny access for the
    // 'edit' operation. Hence, 'edit' and 'delete' should return FALSE.
    $this->condition->setContextValue('op', 'view');
    $this->assertTrue($this->condition->evaluate());
    $this->condition->setContextValue('op', 'edit');
    $this->assertFalse($this->condition->evaluate());
    $this->condition->setContextValue('op', 'delete');
    $this->assertFalse($this->condition->evaluate());
  }
}
