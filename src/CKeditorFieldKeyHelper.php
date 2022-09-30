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
  public static function getElementId(string $elementId): string {
    $elementParts = explode('--', $elementId);
    $id = 'id-' . hash('crc32', reset($elementParts));

    return Html::getId($id);
  }
}
