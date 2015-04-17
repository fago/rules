<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesActionBaseTest.
 */

namespace Drupal\Tests\rules\Unit;

/**
 * @coversDefaultClass \Drupal\rules\Core\RulesActionBase
 * @group rules
 */
class RulesActionBaseTest extends RulesUnitTestBase {

  /**
   * Test the summary is being parsed from the label annotation.
   *
   * @expectedException \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @covers ::summary
   */
  public function testSummaryThrowingException() {
    $rulesActionBase = $this->getMockForAbstractClass('Drupal\rules\Core\RulesActionBase', [[], '', '']);
    $rulesActionBase->summary();
  }

  /**
   * Test the summary is being parsed from the label annotation.
   *
   * @covers ::summary
   */
  public function testSummaryParsingTheLabelAnnotation() {
    $rulesActionBase = $this->getMockForAbstractClass('Drupal\rules\Core\RulesActionBase', [[], '', ['label' => 'something']]);
    $this->assertEquals('something', $rulesActionBase->summary());
  }

}
