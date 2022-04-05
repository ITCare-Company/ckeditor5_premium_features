<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

/**
 * Defines method for storages providing data to be passed to the CKEditor.
 */
interface EditorDataStorageProviderInterface {

  /**
   * Loads the editor plugins data by the given entity IDs.
   *
   * @param array $ids
   *   The IDs of the entity to be fetched.
   *
   * @return array
   *   The normalized data to be consumed by the editor plugins.
   */
  public function loadEditorDataFromIds(array $ids): array;

}
