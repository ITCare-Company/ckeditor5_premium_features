<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Defines a custom text format render preprocess to save collaboration data from XSS filtering.
 */
class TextFormatPreRender implements TrustedCallbackInterface {

  /**
   * {@inheritdoc }
   */
  public static function trustedCallbacks(): array {
    return ['preRenderBefore', 'preRenderAfter'];
  }

  /**
   * Masks collaboration tags name attribute from XSS filtering.
   *
   * @param array $element
   *   Form element.
   */
  public static function preRenderBefore(array $element): array {
    self::preprocessElementValue($element, "self::replaceSecurity");

    return $element;
  }

  /**
   * Reverts masking of collaboration tags name attribute.
   *
   * @param array $element
   *   Form element.
   */
  public static function preRenderAfter(array $element): array {
    self::preprocessElementValue($element, "self::revertSecurityReplace");

    return $element;
  }

  /**
   * Performs collaboration name attribute masking/unmasking.
   *
   * @param array $element
   *   Form element
   * @param string $callback
   *   Callback to be used to process collaboration tag..
   *
   * @return array
   */
  protected static function preprocessElementValue(array &$element, string $callback): array {
    $tagList = [
      'suggestion-start',
      'suggestion-end',
      'comment-start',
      'comment-end'
    ];

    $original = $element['value']['#value'];
    $attributePattern = '[^<>]+name=[^<>]+';

    foreach ($tagList as $tagName) {
      $original = preg_replace_callback(
        "/$tagName$attributePattern/si", $callback, $original);
    }

    $element['value']['#value'] = $original;

    return $element;
  }

  /**
   * Masks collaboration tag attribute.
   *
   * @param $tag
   *   HTML tag.
   */
  protected static function replaceSecurity($tag): string {
    return str_replace(":", "##", $tag[0]);
  }

  /**
   * Unmask collaboration tag attribute.
   *
   * @param $tag
   *   HTML tag.
   */
  protected static function revertSecurityReplace($tag): string {
    return str_replace("##", ":", $tag[0]);
  }

}
