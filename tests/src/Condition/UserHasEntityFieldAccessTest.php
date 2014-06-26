<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserHasEntityFieldAccessTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess;
use Drupal\rules\Tests\RulesTestBase;

/**
 * Tests the 'User has entity field access' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess
 *
 * @see \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess
 */
class UserHasEntityFieldAccessTest extends RulesTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

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

    $this->condition = new UserHasEntityFieldAccess([], '', ['context' => [
      'entity' => new ContextDefinition('entity'),
      'field' => new ContextDefinition('string'),
      'operation' => new ContextDefinition('string'),
      'user' => new ContextDefinition('entity:user'),
    ]], $this->entityManager);

    $this->condition->setStringTranslation($this->getMockStringTranslation());
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
   * Tests evaluating the condition.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluation() {
    $account = $this->getMock('Drupal\user\UserInterface');
    $entity = $this->getMock('Drupal\Core\Entity\ContentEntityInterface');
    $items = $this->getMock('Drupal\Core\Field\FieldItemListInterface');

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

    $this->condition->setContextValue('entity', $this->getMockTypedData($entity))
      ->setContextValue('field', $this->getMockTypedData('potato-field'))
      ->setContextValue('user', $this->getMockTypedData($account));

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
    $this->condition->setContextValue('operation', $this->getMockTypedData('view'));
    $this->assertTrue($this->condition->evaluate());
    $this->condition->setContextValue('operation', $this->getMockTypedData('edit'));
    $this->assertFalse($this->condition->evaluate());
    $this->condition->setContextValue('operation', $this->getMockTypedData('delete'));
    $this->assertFalse($this->condition->evaluate());
  }
}
