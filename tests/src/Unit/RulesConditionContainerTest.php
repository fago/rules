<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesConditionContainerTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Engine\ConditionExpressionContainer;

/**
 * @coversDefaultClass \Drupal\rules\Engine\ConditionExpressionContainer
 * @group rules
 */
class RulesConditionContainerTest extends RulesUnitTestBase {

  /**
   * Creates a mocked condition container.
   *
   * @param array $methods
   *   The methods to mock.
   * @param string $class
   *   The name of the created mock class.
   *
   * @return \Drupal\rules\Engine\ConditionExpressionContainerInterface
   *   The mocked condition container.
   */
  protected function getMockConditionContainer(array $methods = [], $class = 'RulesConditionContainerMock') {
    return $this->getMockForAbstractClass(
      'Drupal\rules\Engine\ConditionExpressionContainer', [], $class, FALSE, TRUE, TRUE, $methods
    );
  }

  /**
   * Tests adding conditions to the condition container.
   *
   * @covers ::addExpressionObject
   */
  public function testAddExpressionObject() {
    $container = $this->getMockConditionContainer();
    $container->addExpressionObject($this->trueConditionExpression);

    $property = new \ReflectionProperty($container, 'conditions');
    $property->setAccessible(TRUE);

    $this->assertArrayEquals([$this->trueConditionExpression], $property->getValue($container));
  }

  /**
   * Tests negating the result of the condition container.
   *
   * @covers ::negate
   * @covers ::isNegated
   */
  public function testNegate() {
    $container = $this->getMockForAbstractClass('Drupal\Tests\rules\Unit\RulesConditionContainerTestStub', [], '', FALSE);

    $this->assertFalse($container->isNegated());
    $this->assertTrue($container->execute());

    $container->negate(TRUE);
    $this->assertTrue($container->isNegated());
    $this->assertFalse($container->execute());
  }

  /**
   * Tests executing the condition container.
   *
   * @covers ::execute
   */
  public function testExecute() {
    $container = $this->getMockForAbstractClass('Drupal\Tests\rules\Unit\RulesConditionContainerTestStub', [], '', FALSE);
    $this->assertTrue($container->execute());
  }

  /**
   * Tests executing the condition container with an executable manager.
   *
   * @covers ::execute
   */
  public function testExecuteWithExecutableManager() {
    $container = $this->getMockConditionContainer(['evaluate']);
    $container->expects($this->never())
      ->method('evaluate');

    $manager = $this->getMock('Drupal\Core\Executable\ExecutableManagerInterface');
    $manager->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $container->setExecutableManager($manager);
    $this->assertTrue($container->execute());
  }

}

/**
 * Class used for overriding evalute() as this does not work with PHPunit.
 */
abstract class RulesConditionContainerTestStub extends ConditionExpressionContainer {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    return TRUE;
  }

}