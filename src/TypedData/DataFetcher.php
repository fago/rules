<?php

/**
 * @file
 * Contains Drupal\rules\TypedData\DataFetcher.
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Implementation of the data fetcher service.
 */
class DataFetcher implements DataFetcherInterface {

  /**
   * {@inheritdoc}
   */
  public function fetchByPropertyPath(TypedDataInterface $typed_data, $property_path, $langcode = NULL) {
    $sub_paths = explode('.', $property_path);
    return $this->fetchBySubPaths($typed_data, $sub_paths, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchBySubPaths(TypedDataInterface $typed_data, array $sub_paths, $langcode = NULL) {
    $current_selector = '';

    try {
      foreach ($sub_paths as $name) {
        $current_selector = $current_selector ? $current_selector . '.' . $name : $name;

        // If the current data is just a reference then directly dereference the
        // target.
        if ($typed_data instanceof DataReferenceInterface) {
          $typed_data = $typed_data->getTarget();
          if ($typed_data === NULL) {
            throw new MissingDataException("The specified reference is NULL.");
          }
        }

        // Make sure we are using the right language.
        if (isset($langcode) && $typed_data instanceof TranslatableInterface) {
          if ($typed_data->hasTranslation($langcode)) {
            $typed_data = $typed_data->getTranslation($langcode);
          }
          // @todo What if the requested translation does not exist? Currently
          // we just ignore that and continue with the current object.
        }

        // If this is a list but the selector is not an integer, we forward the
        // selection to the first element in the list.
        if ($typed_data instanceof ListInterface && !ctype_digit($name)) {
          $typed_data = $typed_data->get(0);
        }

        // Drill down to the next step in the data selector.
        if ($typed_data instanceof ListInterface || $typed_data instanceof ComplexDataInterface) {
          $typed_data = $typed_data->get($name);
        }
        else {
          throw new \InvalidArgumentException("The parent property is not a list or a complex structure.");
        }
      }
      return $typed_data;
    }
    catch (MissingDataException $e) {
      $selector = implode('.', $sub_paths);
      throw new MissingDataException("Unable to apply data selector '$selector' at '$current_selector': " . $e->getMessage());
    }
    catch (\InvalidArgumentException $e) {
      $selector = implode('.', $sub_paths);
      throw new \InvalidArgumentException("Unable to apply data selector '$selector' at '$current_selector': " . $e->getMessage());
    }
  }

}
