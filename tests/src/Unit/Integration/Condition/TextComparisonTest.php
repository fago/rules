<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\TextComparison
 * @group RulesCondition
 */
class TextComparisonTest extends RulesIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = $this->conditionManager->createInstance('rules_text_comparison');
  }

  /**
   * Tests evaluating the condition with the "starts" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorStarts() {
    // Test that when the text string starts with the match string and
    // the operator is 'starts', TRUE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'starts')
      ->setContextValue('match', 'my');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the text string does not start with the match string and
    // the operator is 'starts', FALSE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'starts')
      ->setContextValue('match', 'text');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "ends" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorEnds() {
    // Test that when the text string ends with the match string and
    // the operator is 'ends', TRUE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'ends')
      ->setContextValue('match', 'text');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the text string does not end with the match string and
    // the operator is 'ends', FALSE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'ends')
      ->setContextValue('match', 'my');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "contains" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorContains() {
    // Test that when the text string contains the match string and
    // the operator is 'contains', TRUE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'contains')
      ->setContextValue('match', 'y-t');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the text string does not contain the match string and
    // the operator is 'contains', FALSE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'contains')
      ->setContextValue('match', 't-y');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition with the "regex" operator.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationOperatorRegex() {
    // Test that when the operator is 'regex' and the regular expression in
    // the match string matches the text string, TRUE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'regex')
      ->setContextValue('match', 'me?y-texx?t');
    $this->assertTrue($this->condition->evaluate());

    // Test that when the operator is 'regex' and the regular expression in
    // the match string does not matche the text string, TRUE is returned.
    $this->condition
      ->setContextValue('text', 'my-text')
      ->setContextValue('operator', 'regex')
      ->setContextValue('match', 'me+y-texx?t');
    $this->assertFalse($this->condition->evaluate());
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Text comparison', $this->condition->summary());
  }

}
