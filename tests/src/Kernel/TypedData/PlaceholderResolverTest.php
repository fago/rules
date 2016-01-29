<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\TypedData\PlaceholderResolverTest.
 */

namespace Drupal\Tests\rules\Kernel\TypedData;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the placeholder resolver.
 *
 * @group rules
 *
 * @cover \Drupal\rules\TypedData\PlaceholderResolver
 */
class PlaceholderResolverTest extends KernelTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * A node used for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * An entity type manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The placeholder resolver instanced tested.
   *
   * @var \Drupal\rules\TypedData\PlaceholderResolver
   */
  protected $placeholderResolver;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'system', 'node', 'field', 'text', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->container->get('typed_data_manager');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->placeholderResolver = $this->container->get('typed_data.placeholder_resolver');

    $this->entityTypeManager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    // Create a multi-value integer field for testing.
    FieldStorageConfig::create([
      'field_name' => 'field_integer',
      'type' => 'integer',
      'entity_type' => 'node',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_integer',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $this->node = $this->entityTypeManager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);

    // Make sure default date formats are there for testing the format_date
    // filter.
    $this->installConfig(['system']);
  }

  /**
   * @cover scan
   */
  public function testScanningForPlaceholders() {
    $text = 'token {{example.foo}} and {{example.foo.bar}} just as {{example.foo|default(bar)}} and {{ example.whitespace }}';
    $placeholders = $this->placeholderResolver->scan($text);
    $this->assertEquals([
      'example' => [
        'foo' => '{{example.foo}}',
        'foo.bar' => '{{example.foo.bar}}',
        'foo|default(bar)' => '{{example.foo|default(bar)}}',
        'whitespace' => '{{ example.whitespace }}',
      ],
    ], $placeholders);
    // Test a simple placeholder with filters only.
    $text = "text {{ date | filter }} text";
    $placeholders = $this->placeholderResolver->scan($text);
    $this->assertEquals([
      'date' => [
        '| filter' => '{{ date | filter }}',
      ],
    ], $placeholders);
  }

  /**
   * @cover resolvePlaceholders
   */
  public function testResolvingPlaceholders() {
    $text = 'test {{node.title}} and {{node.title.value}}';
    $result = $this->placeholderResolver->resolvePlaceholders($text, ['node' => $this->node->getTypedData()]);
    $expected = [
      '{{node.title}}' => 'test',
      '{{node.title.value}}' => 'test',
    ];
    $this->assertEquals($expected, $result);

    // Test a placeholder without accessing a property.
    $text = 'test {{string}}';
    $result = $this->placeholderResolver->resolvePlaceholders($text, [
      'string' => $this->typedDataManager->create(
      DataDefinition::create('string'), 'replacement'
      ),
    ]);
    $expected = [
      '{{string}}' => 'replacement',
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testReplacePlaceholders() {
    $text = 'test {{node.title}} and {{node.title.value}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test test and test', $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testPlaceholdersAcrossReferences() {
    $user = $this->entityTypeManager->getStorage('user')
      ->create([
        'name' => 'test',
        'type' => 'user',
      ]);
    $this->node->uid->entity = $user;
    $text = 'test {{node.title}} and {{node.uid.entity.name}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test test and test', $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testPlaceholdersWithMissingData() {
    $text = 'test {{node.title.1.value}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()], NULL, []);
    $this->assertEquals('test {{node.title.1.value}}', $result);
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()], NULL, ['clear' => FALSE]);
    $this->assertEquals('test {{node.title.1.value}}', $result);
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()], NULL, ['clear' => TRUE]);
    $this->assertEquals('test ', $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testStringEncoding() {
    $this->node->title->value = '<b>XSS</b>';
    $text = 'test {{node.title}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test ' . new HtmlEscapedText('<b>XSS</b>'), $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testIntegerPlaceholder() {
    $this->node->field_integer->value = 3;
    $text = 'test {{node.field_integer.0.value}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test 3', $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testListPlaceholder() {
    $this->node->field_integer = [1, 2];
    $text = 'test {{node.field_integer}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test 1, 2', $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testApplyingFilters() {
    $this->node->field_integer = [1, 2, NULL];
    $this->node->title->value = NULL;
    $result = $this->placeholderResolver->replacePlaceHolders("test {{node.field_integer.2.value|default('0')}}", ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test 0', $result);
    $result = $this->placeholderResolver->replacePlaceHolders("test {{node.title.value|default('tEsT')|lower}}", ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test test', $result);

    // Test filter expressions with whitespaces.
    $result = $this->placeholderResolver->replacePlaceHolders("test {{ node.title.value | default('tEsT') | lower }}", ['node' => $this->node->getTypedData()]);
    $this->assertEquals('test test', $result);

    // Test a filter expression on data without accessing a property.
    $text = 'test {{string | lower}}';
    $result = $this->placeholderResolver->replacePlaceHolders($text, [
      'string' => $this->typedDataManager->create(DataDefinition::create('string'), 'Replacement'),
    ]);
    $this->assertEquals('test replacement', $result);

    $text = "The year is {{ date | format_date('custom', 'Y') }}.";
    $result = $this->placeholderResolver->replacePlaceHolders($text, [
      'date' => $this->typedDataManager->create(DataDefinition::create('timestamp'), '3600'),
    ]);
    $this->assertEquals('The year is 1970.', $result);
  }

  /**
   * @cover replacePlaceHolders
   */
  public function testBubbleableMetadata() {
    // Make sure the bubbleable metadata added by the fetcher is properly passed
    // though.
    $bubbleable_metadata = new BubbleableMetadata();
    // Save the node, so it gets a cache tag.
    $this->node->save();
    $this->placeholderResolver->replacePlaceHolders('test {{node.field_integer}}', ['node' => $this->node->getTypedData()], $bubbleable_metadata);
    $expected = ['node:' . $this->node->id()];
    $this->assertEquals($expected, $bubbleable_metadata->getCacheTags());

    // Ensure cache tags of filters are added in.
    $bubbleable_metadata = new BubbleableMetadata();
    $this->placeholderResolver->replacePlaceHolders("test {{ node.created.value | format_date('medium') }}", ['node' => $this->node->getTypedData()], $bubbleable_metadata);
    $expected = Cache::mergeTags(['node:' . $this->node->id()], DateFormat::load('medium')->getCacheTags());
    $this->assertEquals($expected, $bubbleable_metadata->getCacheTags());
  }

}
