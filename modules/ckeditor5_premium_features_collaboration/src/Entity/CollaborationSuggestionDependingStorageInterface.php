<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

/**
 * Defines the storage depending on suggestion entities.
 */
interface CollaborationSuggestionDependingStorageInterface {

  public function setSuggestionIds(array $suggestion_ids): void;

  public function hasSuggestionId(string $suggestion_id): bool;

}
