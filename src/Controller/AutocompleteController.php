<?php

namespace Drupal\rules\Controller;

use Drupal\rules\Ui\RulesUiHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles autocompletion of data selectors.
 */
class AutocompleteController {

  /**
   * Returns a JSON list of autocomplete suggestions for data selectors.
   *
   * @param \Drupal\rules\Ui\RulesUiHandlerInterface $rules_ui_handler
   *   The UI handler.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object providing the autocomplete query parameter.
   * @param string $uuid
   *   The UUID of the expression in which the autocomplete is triggered. If the
   *   UUID is not provided all available variables from the end of the
   *   expression will be shown.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON results.
   */
  public function autocomplete(RulesUiHandlerInterface $rules_ui_handler, Request $request, $uuid = NULL) {
    $component = $rules_ui_handler->getComponent();
    $nested_expression = $component->getExpression()->getExpression($uuid);
    if ($nested_expression === FALSE) {
      // @todo We don't have a UUID when an expression is added. Just show all
      // variables available in that case. The correct solution would be to get
      // the form state of the expression form currently being added. That is
      // very complicated so we don't do it for now.
      $nested_expression = NULL;
    }

    $string = $request->query->get('q');
    $results = $component->autocomplete($string, $nested_expression);

    return new JsonResponse($results);
  }

}
