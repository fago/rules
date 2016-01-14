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
 *
 * This is a Typed Data based alternative to the token service, see
 * \Drupal\Core\Utility\Token.
 */
class PlaceholderResolver {

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
   * Replaces all placeholder tokens in a given string with appropriate values.
   *
   * @param string $text
   *   An HTML string containing replaceable tokens. The caller is responsible
   *   for calling \Drupal\Component\Utility\Html::escape() in case the $text
   *   was plain text.
   * @param \Drupal\Core\TypedData\TypedDataInterface[] $data
   *   The data to use for generating values for the placeholder, keyed by
   *   name.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process. Supported options are:
   *   - langcode: A language code to be used when generating locale-sensitive
   *     tokens.
   *   - clear: A boolean flag indicating that tokens should be removed from the
   *     final text if no replacement value can be generated. Defaults to FALSE.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata|null
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *   Refer to ::replacePlaceHolders() for further details.
   *
   * @return \Drupal\Component\Render\MarkupInterface[]
   *   An array of replacement values for the placeholders contained in the
   *   text, keyed by placeholder.
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
   * Replaces the placeholders in the given text.
   *
   * To ensure that the metadata associated with the token replacements gets
   * attached to the render array that contains the token-replaced text, callers
   * of this method are encouraged to pass in a BubbleableMetadata object and
   * apply it to the corresponding render array. For example:
   * @code
   *     $bubbleable_metadata = new BubbleableMetadata();
   *     $build['#markup'] = $token_service->replacePlaceHolders('Tokens: [node:nid] [current-user:uid]', ['node' => $node->getTypedData()], [], $bubbleable_metadata);
   *     $bubbleable_metadata->applyTo($build);
   * @endcode
   *
   * @param string $text
   *   The text containing the placeholders.
   * @param \Drupal\Core\TypedData\TypedDataInterface[] $data
   *   The data to use for generating values for the placeholder, keyed by
   *   name.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process. Supported options are:
   *   - langcode: A language code to be used when generating locale-sensitive
   *     tokens.
   *   - clear: A boolean flag indicating that tokens should be removed from the
   *     final text if no replacement value can be generated. Defaults to FALSE.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata|null
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *
   * @return string
   *   The result is the entered HTML text with tokens replaced. The
   *   caller is responsible for choosing the right escaping / sanitization. If
   *   the result is intended to be used as plain text, using
   *   PlainTextOutput::renderFromHtml() is recommended. If the result is just
   *   printed as part of a template relying on Twig autoescaping is possible,
   *   otherwise for example the result can be put into #markup, in which case
   *   it would be sanitized by Xss::filterAdmin().
   */
  public function replacePlaceHolders($text, array $data = [], array $options = [], BubbleableMetadata $bubbleable_metadata = NULL) {
    $replacements = $this->resolvePlaceholders($text, $data, $options, $bubbleable_metadata);

    $placeholders = array_keys($replacements);
    $values = array_values($replacements);

    return str_replace($placeholders, $values, $text);
  }

  /**
   * Builds a list of all placeholder tokens that appear in the text.
   *
   * @param string $text
   *   The text to be scanned for possible tokens.
   *
   * @return array
   *   An associative array of discovered tokens, grouped by data name.
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
