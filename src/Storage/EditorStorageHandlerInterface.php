<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Storage;

/**
 * Defines the interface for the handlers of the editor storage.
 */
interface EditorStorageHandlerInterface {

  /**
   * Checks if the editor of the given form element is CKEditor5.
   *
   * @param array $element
   *   The form element with the editor format defined.
   *
   * @return bool
   *   True if it is using CKEditor5, false otherwise.
   */
  public function isCkeditor5(array $element): bool;

  /**
   * Checks if any collaboration feature is enabled.
   *
   * @param array $element
   *   The form element with the editor format defined.
   *
   * @return bool
   *   True if it any of the plugin is collaboration feature, false otherwise.
   */
  public function hasCollaborationFeaturesEnabled(array $element): bool;

}
