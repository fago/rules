<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\TypedData\DataFiterTest.
 */

namespace Drupal\Tests\rules\Kernel\TypedData;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;


/**
 * Tests using typed data filters.
 *
 * @group rules
 *
 * @cover \Drupal\rules\TypedData\DataFilterManager
 */
class DataFiterTest extends KernelTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The data filter manager.
   *
   * @var \Drupal\rules\TypedData\DataFilterManagerInterface
   */
  protected $dataFilterManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->container->get('typed_data_manager');
    $this->dataFilterManager = $this->container->get('plugin.manager.typed_data_filter');

    // Make sure default date formats are there for testing the format_date
    // filter.
    $this->installConfig(['system']);
  }

  /**
   * @cover \Drupal\rules\Plugin\TypedDataFilter\LowerFilter
   */
  public function testLowerFilter() {
    $filter = $this->dataFilterManager->createInstance('lower');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), 'tEsT');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $this->assertEquals('test', $filter->filter($data->getDataDefinition(), $data->getValue(), []));
  }

  /**
   * @cover \Drupal\rules\Plugin\TypedDataFilter\DefaultFilter
   */
  public function testDefaultFilter() {
    $filter = $this->dataFilterManager->createInstance('default');
    $data = $this->typedDataManager->create(DataDefinition::create('string'));

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertSame($data->getDataDefinition(), $filter->filtersTo($data->getDataDefinition(), ['default']));

    $fails = $filter->validateArguments($data->getDataDefinition(), []);
    $this->assertEquals(1, count($fails));
    $this->assertContains('Missing arguments', (string) $fails[0]);
    $fails = $filter->validateArguments($data->getDataDefinition(), [new \StdClass()]);
    $this->assertEquals(1, count($fails));
    $this->assertEquals('This value should be of the correct primitive type.', $fails[0]);

    $this->assertEquals('default', $filter->filter($data->getDataDefinition(), $data->getValue(), ['default']));
    $data->setValue('non-default');
    $this->assertEquals('non-default', $filter->filter($data->getDataDefinition(), $data->getValue(), ['default']));
  }

  /**
   * @cover \Drupal\rules\Plugin\TypedDataFilter\FormatDateFilter
   */
  public function testFormatDateFilter() {
    $filter = $this->dataFilterManager->createInstance('format_date');
    $data = $this->typedDataManager->create(DataDefinition::create('timestamp'), 3700);

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $fails = $filter->validateArguments($data->getDataDefinition(), []);
    $this->assertEquals(0, count($fails));
    $fails = $filter->validateArguments($data->getDataDefinition(), ['medium']);
    $this->assertEquals(0, count($fails));
    $fails = $filter->validateArguments($data->getDataDefinition(), ['invalid-format']);
    $this->assertEquals(1, count($fails));
    $fails = $filter->validateArguments($data->getDataDefinition(), ['custom']);
    $this->assertEquals(1, count($fails));
    $fails = $filter->validateArguments($data->getDataDefinition(), ['custom', 'Y']);
    $this->assertEquals(0, count($fails));

    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $this->container->get('date.formatter');
    $this->assertEquals($date_formatter->format(3700), $filter->filter($data->getDataDefinition(), $data->getValue(), []));
    $this->assertEquals($date_formatter->format(3700, 'short'), $filter->filter($data->getDataDefinition(), $data->getValue(), ['short']));
    $this->assertEquals('1970', $filter->filter($data->getDataDefinition(), $data->getValue(), ['custom', 'Y']));

    // Verify the filter works with non-timestamp data as well.
    $data = $this->typedDataManager->create(DataDefinition::create('datetime_iso8601'), "1970-01-01T10:10:10+00:00");
    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());
    $this->assertEquals('1970', $filter->filter($data->getDataDefinition(), $data->getValue(), ['custom', 'Y']));

    // Test cache dependencies of date format config entities are added in.
    $metadata = new BubbleableMetadata();
    $filter->filter($data->getDataDefinition(), $data->getValue(), ['short'], $metadata);
    $this->assertEquals(DateFormat::load('short')->getCacheTags(), $metadata->getCacheTags());
    $metadata = new BubbleableMetadata();
    $filter->filter($data->getDataDefinition(), $data->getValue(), ['custom', 'Y'], $metadata);
    $this->assertEquals([], $metadata->getCacheTags());
  }

}
