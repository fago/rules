<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\PlaceholderResolverInterface.
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Interface for a placeholder resolver based upon typed data.
 *
 * This is a Typed Data based alternative to the token service, see
 * \Drupal\Core\Utility\Token.
 */
interface PlaceholderResolverInterface {

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
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which required bubbleable metadata will be added.
   *   Refer to ::replacePlaceHolders() for further details.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process. Supported options are:
   *   - langcode: A language code to be used when generating locale-sensitive
   *     tokens.
   *   - clear: A boolean flag indicating that tokens should be removed from the
   *     final text if no replacement value can be generated. Defaults to FALSE.
   *
   * @return \Drupal\Component\Render\MarkupInterface[]
   *   An array of replacement values for the placeholders contained in the
   *   text, keyed by placeholder.
   */
  public function resolvePlaceholders($text, array $data = [], BubbleableMetadata $bubbleable_metadata = NULL, array $options = []);

  /**
   * Replaces the placeholders in the given text.
   *
   * To ensure that the metadata associated with the token replacements gets
   * attached to the render array that contains the token-replaced text,
   * callers
   * of this method are encouraged to pass in a BubbleableMetadata object and
   * apply it to the corresponding render array. For example:
   *
   * @code
   *   $bubbleable_metadata = new BubbleableMetadata();
   *   $build['#markup'] = $resolver->replacePlaceHolders('Tokens: [node:nid] [current-user:uid]', ['node' => $node->getTypedData()], [], $bubbleable_metadata);
   *   $bubbleable_metadata->applyTo($build);
   * @endcode
   *
   * @param string $text
   *   The text containing the placeholders.
   * @param \Drupal\Core\TypedData\TypedDataInterface[] $data
   *   The data to use for generating values for the placeholder, keyed by
   *   name.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which required bubbleable metadata will be added.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process. Supported options are:
   *   - langcode: A language code to be used when generating locale-sensitive
   *     tokens.
   *   - clear: A boolean flag indicating that tokens should be removed from the
   *     final text if no replacement value can be generated. Defaults to
   *     FALSE.
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
  public function replacePlaceHolders($text, array $data = [], BubbleableMetadata $bubbleable_metadata = NULL, array $options = []);

  /**
   * Builds a list of all placeholder tokens that appear in the text.
   *
   * @param string $text
   *   The text to be scanned for possible tokens.
   *
   * @return array
   *   An associative array of discovered tokens, grouped by data name.
   */
  public function scan($text);

}
