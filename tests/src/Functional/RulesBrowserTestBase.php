<?php

namespace Drupal\Tests\rules\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Has some additional helper methods to make test code more readable.
 */
abstract class RulesBrowserTestBase extends BrowserTestBase {

  /**
   * Finds link with specified locator.
   *
   * @param string $locator
   *   Link id, title, text or image alt.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The link node element.
   */
  public function findLink($locator) {
    return $this->getSession()->getPage()->findLink($locator);
  }

  /**
   * Finds field (input, textarea, select) with specified locator.
   *
   * @param string $locator
   *   Input id, name or label.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The input field element.
   */
  public function findField($locator) {
    return $this->getSession()->getPage()->findField($locator);
  }

  /**
   * Finds button with specified locator.
   *
   * @param string $locator
   *   Button id, value or alt.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The button node element.
   */
  public function findButton($locator) {
    return $this->getSession()->getPage()->findButton($locator);
  }

  /**
   * Presses button with specified locator.
   *
   * @param string $locator
   *   Button id, value or alt.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function pressButton($locator) {
    $this->getSession()->getPage()->pressButton($locator);
  }

  /**
   * Fills in field (input, textarea, select) with specified locator.
   *
   * @param string $locator
   *   Input id, name or label.
   * @param string $value
   *   Value.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   *
   * @see \Behat\Mink\Element\NodeElement::setValue
   */
  public function fillField($locator, $value) {
    $this->getSession()->getPage()->fillField($locator, $value);
  }

}
