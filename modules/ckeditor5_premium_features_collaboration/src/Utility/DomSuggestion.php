<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

/**
 * Provides the utility class for accessing suggestions data in DOM.
 */
class DomSuggestion {

  /**
   * Creates the DOM suggestion element.
   *
   * @param \DOMElement $element
   *   The DOM elemement.
   */
  public function __construct(protected \DOMElement $element) {
  }

  /**
   * Gets the suggestion type.
   *
   * @return string
   *   The suggestion type.
   */
  public function getType(): string {
    $value = $this->isStartTag() || $this->isEndTag()
      ? $this->getNameAttributeValue() : ($this->getStartAttributeValue() ?? $this->getEndAttributeValue());

    [$type, $id, $uid] = explode(':', $value);

    return $type;
  }

  /**
   * Gets the name attribute value.
   *
   * @return string
   *   The attribute value.
   */
  public function getNameAttributeValue(): string {
    return $this->element->getAttribute('name');
  }

  /**
   * Gets the data attribute value of the suggestion end.
   *
   * @return string
   *   The attribute value.
   */
  public function getEndAttributeValue(): string {
    return $this->element->getAttribute('data-suggestion-end-after');
  }

  /**
   * Gets the data attribute value of the suggestion start.
   *
   * @return string
   *   The attribute value.
   */
  public function getStartAttributeValue(): string {
    return $this->element->getAttribute('data-suggestion-start-before');
  }

  /**
   * Check if this is the suggestion closing tag (end).
   *
   * @return bool
   *   True if end tag, false otherwise.
   */
  public function isEndTag(): bool {
    return $this->element->nodeName === 'suggestion-end';
  }

  /**
   * Check if this is the suggestion opening tag (start).
   *
   * @return bool
   *   True if start tag, false otherwise.
   */
  public function isStartTag(): bool {
    return $this->element->nodeName === 'suggestion-start';
  }

  /**
   * Checks if the given element is of type insertion.
   *
   * @return bool
   *   True if this is the insertion, false otherwise.
   */
  public function isInsertion(): bool {
    return $this->getType() === 'insertion';
  }

  /**
   * Compares the name attribute value with the given one.
   *
   * @param string $name
   *   The name to be used in comparison.
   *
   * @return bool
   *   True if the names are equal, false otherwise.
   */
  public function hasName(string $name): bool {
    return $this->getNameAttributeValue() === $name;
  }

}
