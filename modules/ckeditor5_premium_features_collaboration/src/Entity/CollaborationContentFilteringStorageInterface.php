<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\filter\FilterFormatInterface;

/**
 * Defines the storage doing content filtering.
 */
interface CollaborationContentFilteringStorageInterface {

  public function setSourceFilterFormat(FilterFormatInterface $filter_format): void;

  public function filterSourceData(array &$source_data): void;

}
