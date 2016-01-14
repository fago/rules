<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\PlaceholderResolver.
 */

namespace Drupal\rules\TypedData;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Resolver for placeholder tokens based upon typed data.
 */
class PlaceholderResolver implements PlaceholderResolverInterface {

  /**
   * The typed data manager.
   *
   * @var \Drupal\rules\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\rules\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(TypedDataManagerInterface $typed_data_manager) {
    $this->typedDataManager = $typed_data_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolvePlaceholders($text, array $data = [], array $options = [], BubbleableMetadata $bubbleable_metadata = NULL) {
    $options += [
      'langcode' => NULL,
      'clear' => FALSE,
    ];
    $placeholder_by_data = $this->scan($text);
    if (empty($placeholder_by_data)) {
      return $text;
    }

    // @todo: Add metadata from each step while fetching data.
    $bubbleable_metadata = $bubbleable_metadata ?: new BubbleableMetadata();
    foreach ($data as $object) {
      if ($object instanceof CacheableDependencyInterface || $object instanceof AttachmentsInterface) {
        $bubbleable_metadata->addCacheableDependency($object);
      }
    }

    $replacements = [];
    $data_fetcher = $this->typedDataManager->getDataFetcher();
    foreach ($placeholder_by_data as $data_name => $placeholders) {
      foreach ($placeholders as $sub_path => $placeholder) {
        try {
          if (!isset($data[$data_name])) {
            throw new MissingDataException();
          }
          $fetched_data = $data_fetcher->fetchBySubPaths($data[$data_name], explode(':', $sub_path), $options['langcode']);
          $value = $fetched_data->getString();
          // @todo: Add token formatting support here.
          // Escape the tokens, unless they are explicitly markup.
          $replacements[$placeholder] = $value instanceof MarkupInterface ? $value : new HtmlEscapedText($value);
        }
        catch (MissingDataException $e) {
          if (!empty($options['clear'])) {
            $replacements[$placeholder] = '';
          }
        }
      }
    }
    return $replacements;
  }

  /**
   * {@inheritdoc}
   */
  public function replacePlaceHolders($text, array $data = [], array $options = [], BubbleableMetadata $bubbleable_metadata = NULL) {
    $replacements = $this->resolvePlaceholders($text, $data, $options, $bubbleable_metadata);

    $placeholders = array_keys($replacements);
    $values = array_values($replacements);

    return str_replace($placeholders, $values, $text);
  }

  /**
   * {@inheritdoc}
   */
  public function scan($text) {
    // Matches tokens with the following pattern: [$name:$property_path]
    // $name and $property_path may not contain [ ] characters.
    // $name may not contain : or whitespace characters, but $property_path may.
    preg_match_all('/
      \[             # [ - pattern start
      ([^\s\[\]:]+)  # match $type not containing whitespace : [ or ]
      :              # : - separator
      ([^\[\]]+)     # match $name not containing [ or ]
      \]             # ] - pattern end
      /x', $text, $matches);

    $names = $matches[1];
    $tokens = $matches[2];

    // Iterate through the matches, building an associative array containing
    // $tokens grouped by $types, pointing to the version of the token found in
    // the source text. For example,
    // $results['node']['title'] = '[node:title]';.
    $results = [];
    for ($i = 0; $i < count($tokens); $i++) {
      $results[$names[$i]][$tokens[$i]] = $matches[0][$i];
    }

    return $results;
  }

}
