<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\UserHasEntityFieldAccessTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess;
use Drupal\rules\Tests\RulesUnitTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\UserHasEntityFieldAccess
 * @group rules_conditions
 */
class UserHasEntityFieldAccessTest extends RulesUnitTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * The mocked entity access handler.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Entity\EntityAccessControlHandlerInterface
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
  public function setUp() {
    parent::setUp();

    $this->entityAccess = $this->getMock('Drupal\Core\Entity\EntityAccessControlHandlerInterface');
    $this->entityManager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->any())
      ->method('getAccessControlHandler')
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
