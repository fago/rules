<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesActionBaseTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\rules\Core\RulesActionBase;

/**
 * @coversDefaultClass \Drupal\rules\Core\RulesActionBase
 * @group rules
 */
class RulesActionBaseTest extends RulesUnitTestBase {

  /**
   * Tests that a missing label throwa an exception.
   *
   * @expectedException \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *
   * @covers ::summary
   */
  public function testSummaryThrowingException() {
    $rulesActionBase = $this->getMockForAbstractClass(RulesActionBase::class, [[], '', '']);
    $rulesActionBase->summary();
  }

  /**
   * Tests that the summary is being parsed from the label annotation.
   *
   * @covers ::summary
   */
  public function testSummaryParsingTheLabelAnnotation() {
    $rulesActionBase = $this->getMockForAbstractClass(RulesActionBase::class, [[], '', ['label' => 'something']]);
    $this->assertEquals('something', $rulesActionBase->summary());
  }

  /**
   * Tests that a translation wrapper label is correctly parsed.
   *
   * @covers ::summary
   */
  public function testTranslatedLabel() {
    $translation_wrapper = $this->prophesize(TranslationWrapper::class);
    $translation_wrapper->__toString()->willReturn('something');
    $rulesActionBase = $this->getMockForAbstractClass(RulesActionBase::class, [[], '', ['label' => $translation_wrapper->reveal()]]);
    $this->assertEquals('something', $rulesActionBase->summary());
  }

}
