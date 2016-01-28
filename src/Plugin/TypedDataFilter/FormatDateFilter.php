<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\TypedDataFilter\FormatDateFilter.
 */

namespace Drupal\rules\Plugin\TypedDataFilter;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Type\DateTimeInterface;
use Drupal\rules\TypedData\DataFilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A data filter for formatting dates.
 *
 * @DataFilter(
 *   id = "format_date",
 *   label = @Translation("Formats a date, using a configured date type or a custom date format string."),
 * )
 */
class FormatDateFilter extends DataFilterBase implements ContainerFactoryPluginInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateFormatStorage;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter to use.
   * @param \Drupal\Core\Entity\EntityStorageInterface $date_format_storage
   *   The date format storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter, EntityStorageInterface $date_format_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
    $this->dateFormatStorage = $date_format_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('entity.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments, BubbleableMetadata $bubbleable_metadata = NULL) {
    if ($definition->getDataType() != 'timestamp') {
      // Convert the date to an timestamp.
      $value = $this->getTypedDataManager()->create($definition, $value)
        ->getDateTime()
        ->getTimestamp();
    }
    $arguments += [0 => 'medium', 1 => '', 2 => NULL, 3 => NULL];
    if ($arguments[0] != 'custom' && $bubbleable_metadata) {
      $config = $this->dateFormatStorage->load($arguments[0]);
      if (!$config) {
        throw new \InvalidArgumentException("Unknown date format $arguments[0] given.");
      }
      $bubbleable_metadata->addCacheableDependency($config);
    }
    return $this->dateFormatter->format($value, $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
  }

  /**
   * {@inheritdoc}
   */
  public function canFilter(DataDefinitionInterface $definition) {
    return is_subclass_of($definition->getClass(), DateTimeInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments) {
    return DataDefinition::create('string');
  }

  /**
   * {@inheritdoc}
   */
  public function validateArguments(DataDefinitionInterface $definition, array $arguments) {
    $fails = parent::validateArguments($definition, $arguments);
    $arguments += [0 => 'medium', 1 => '', 2 => NULL, 3 => NULL];
    if ($arguments[0] != 'custom' && $this->dateFormatStorage->load($arguments[0]) === NULL) {
      $fails[] = $this->t('Unkown date format %format given.', ['%format' => $arguments[0]]);
    }
    if ($arguments[0] != 'custom' && $arguments[1]) {
      $fails[] = $this->t("If a custom date format is supplied, 'custom' must be passed as date format.");
    }
    elseif ($arguments[0] == 'custom' && !$arguments[1]) {
      $fails[] = $this->t("If 'custom' is given as date type, a custom date formatting string must be provided; e.g., 'Y-m-d H:i:s'.");
    }
    return $fails;
    // @todo: Should we validate timezones and langcodes also?
  }

}
