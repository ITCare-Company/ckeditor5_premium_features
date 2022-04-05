<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

/**
 * Provides the interface for the storages that may require normalization.
 */
interface StorageDataNormalizationAwareInterface {

  /**
   * Normalize the storage.
   *
   * It is useful especialy for
   * data before being submited to the storage.
   *
   * @param array $data
   *   The data to be normalized.
   *
   * @return array
   *   The normalized data.
   */
  public function normalize(array $data): array;

}
