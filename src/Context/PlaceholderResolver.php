<?php

/**
 * @file
 * Contains \Drupal\rules\Context\PlaceholderResolver.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\rules\TypedData\TypedDataManagerTrait;

/**
 * Class PlaceholderResolver.
 */
class PlaceholderResolver {

  use TypedDataManagerTrait;

  /**
   * Definitions for the context used.
   *
   * @var \Drupal\rules\Context\ContextDefinitionInterface[]
   */
  protected $contextDefinitions;

  /**
   * Array of data for the defined contexts, keyed by context name.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface[]
   */
  protected $contextData;

  /**
   * The language code to use when resolving replacements.
   *
   * @var string|null
   */
  protected $langcode;

  /**
   * Constructs the object.
   *
   * @return static
   */
  public static function create() {
    return new static();
  }

  /**
   * Constructs the object.
   */
  protected function __construct() { }

  /**
   * Adds a context definition.
   *
   * @param string $name
   *   The name of the context to add.
   * @param \Drupal\rules\Context\ContextDefinitionInterface $definition
   *   The definition to add.
   *
   * @throws \LogicException
   *   Thrown if there is already a context with the given name defined.
   *
   * @return $this
   */
  public function addContextDefinition($name, ContextDefinitionInterface $definition) {
    if (isset($this->contextDefinitions[$name])) {
      throw new \LogicException("A context with the name '$name' is already defined.");
    }
    $this->contextDefinitions[$name] = $definition;
    return $this;
  }

  /**
   * Sets the value of a context.
   *
   * @param string $name
   *   The name.
   * @param mixed $value
   *   The context value.
   *
   * @throws \LogicException
   *   Thrown if the passed context is not defined.
   *
   * @return $this
   */
  public function setContextValue($name, $value) {
    if (!isset($this->contextDefinitions[$name])) {
      throw new \LogicException("The specified context '$name' is not defined.");
    }
    $this->state->addContext($name, $this->contextDefinitions[$name], $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addContext($name, ContextDefinitionInterface $definition, $value) {
    $this->addContextDefinition($name, $definition);
    $data = $this->getTypedDataManager()->create(
      $definition->getDataDefinition(),
      $value
    );
    $this->addContextData($name, $data);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addContextData($name, TypedDataInterface $data) {
    $this->contextData[$name] = $data;
    return $this;
  }

  /**
   * Replaces all placeholder tokens in a given string with appropriate values.
   *
   * @param string $text
   *   An HTML string containing replaceable tokens. The caller is responsible
   *   for calling \Drupal\Component\Utility\Html::escape() in case the $text
   *   was plain text.
   * @param bool $clear_missing
   *   (optional) Whether to clear missing placeholders; i.e., placeholders
   *   having no replacement value are removed from the final text.
   *
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process. Supported options are:
   *   - langcode: A language code to be used when generating locale-sensitive
   *     tokens.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata|null
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *
   *   To ensure that the metadata associated with the token replacements gets
   *   attached to the same render array that contains the token-replaced text,
   *   callers of this method are encouraged to pass in a BubbleableMetadata
   *   object and apply it to the corresponding render array. For example:
   *   @code
   *     $bubbleable_metadata = new BubbleableMetadata();
   *     $build['#markup'] = $token_service->replace('Tokens: [node:nid] [current-user:uid]', ['node' => $node], [], $bubbleable_metadata);
   *     $bubbleable_metadata->applyTo($build);
   *   @endcode
   *
   *   When the caller does not pass in a BubbleableMetadata object, this
   *   method creates a local one, and applies the collected metadata to the
   *   Renderer's currently active render context.
   *
   * @return \Drupal\Component\Render\MarkupInterface[]
   *   An array of replacement values for the placeholders contained in the
   *   text, keyed by placeholder.
   */
  public function resolvePlaceholders($text, $clear_missing = TRUE, BubbleableMetadata $bubbleable_metadata = NULL) {
    $placeholder_by_context = $this->scan($text);
    if (empty($placeholder_by_context)) {
      return $text;
    }

    // @todo: Add metadata from each step while fetching data.
    $bubbleable_metadata = $bubbleable_metadata ?: new BubbleableMetadata();
    foreach ($this->contextData as $object) {
      if ($object instanceof CacheableDependencyInterface || $object instanceof AttachmentsInterface) {
        $bubbleable_metadata->addCacheableDependency($object);
      }
    }

    $replacements = array();
    $data_fetcher = $this->getTypedDataManager()->getDataFetcher();
    foreach ($placeholder_by_context as $name => $placeholders) {
      foreach ($placeholders as $placeholder) {
        try {
          $value = $data_fetcher->fetchBySubPaths($this->contextData[$name], explode(':', $placeholder), $this->langcode);
          // Escape the tokens, unless they are explicitly markup.
          $replacements[$placeholder] = $value instanceof MarkupInterface ? $value : new HtmlEscapedText($value);
        }
        catch (MissingDataException $e) {
          if (!empty($clear_missing)) {
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
   * @param string $text
   *   The text containing the placeholders.
   * @param bool $clear_missing
   *   (optional) Whether to clear missing placeholders.
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
  public function replacePlaceHolders($text, $clear_missing = TRUE) {
    $replacements = $this->resolvePlaceholders($text, $clear_missing);

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
   *   An associative array of discovered tokens, grouped by context name.
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
    // the source text. For example, $results['node']['title'] = '[node:title]';
    $results = array();
    for ($i = 0; $i < count($tokens); $i++) {
      $results[$names[$i]][$tokens[$i]] = $matches[0][$i];
    }

    return $results;
  }

}
