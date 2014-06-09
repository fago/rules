<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesOrTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Plugin\RulesExpression\RulesOr;

/**
 * Tests the rules OR condition plugin.
 */
class RulesOrTest extends RulesTestBase {

  /**
   * The typed data manger.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The 'or' condition container being tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionContainerInterface
   */
  protected $or;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'RulesOr class tests',
      'description' => 'Test the RuleOr class',
      'group' => 'Rules',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->or = new RulesOr([], '', [], $this->typedDataManager);
  }

  /**
   * Tests one condition.
   */
  public function testOneCondition() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->once())
      ->method('execute');

    $this->or->addCondition($this->trueCondition);
    $this->assertTrue($this->or->execute(), 'Single condition returns TRUE.');
  }

  /**
   * Tests an empty OR.
   */
  public function testEmptyOr() {
    $property = new \ReflectionProperty($this->or, 'conditions');
    $property->setAccessible(TRUE);

    $this->assertEmpty($property->getValue($this->or));
    $this->assertTrue($this->or->execute(), 'Empty OR returns TRUE.');
  }

  /**
   * Tests two true condition.
   */
  public function testTwoConditions() {
    // The method on the test condition must be called once.
    $this->trueCondition->expects($this->once())
      ->method('execute');

    $this->or->addCondition($this->trueCondition)
      ->addCondition($this->trueCondition);

    $this->assertTrue($this->or->execute(), 'Two conditions returns TRUE.');
  }

  /**
   * Tests two false conditions.
   */
  public function testTwoFalseConditions() {
    // The method on the test condition must be called once.
    $this->falseCondition->expects($this->exactly(2))
      ->method('execute');

    $this->or->addCondition($this->falseCondition)
      ->addCondition($this->falseCondition);

    $this->assertFalse($this->or->execute(), 'Two false conditions return FALSE.');
  }
}
