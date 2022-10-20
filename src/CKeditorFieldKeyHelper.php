<?php

namespace Drupal\ckeditor5_premium_features;

use Drupal\Component\Utility\Html;

class CKeditorFieldKeyHelper {

  /**
   * Gets the element unique HTML ID.
   *
   * @param string $elementId
   *   Form element ID.
   *
   * @return string
   *   The ID.
   */
  public static function getElementUniqueId(string $elementId): string {
    $id = 'id-' . hash('crc32', static::cleanElementDrupalId($elementId));

    return Html::getId($id);
  }

  /**
   * Returns cleaned form element ID (without "--POSTFIX").
   *
   * @param string $elementId
   *   Form element ID.
   */
  public static function cleanElementDrupalId(string $elementId): string {
    $elementParts = explode('--', $elementId);

    return reset($elementParts);
  }
}
