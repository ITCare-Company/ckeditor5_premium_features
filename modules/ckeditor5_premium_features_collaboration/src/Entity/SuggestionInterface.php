<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides the interface for the the CKEditor5 "Suggestion" entity.
 */
interface SuggestionInterface extends ContentEntityInterface {

  public const ENTITY_TYPE_ID = 'ckeditor5_suggestion';

  /**
   * Gets the node creation timestamp.
   *
   * @return int
   *   Creation timestamp of the node.
   */
  public function getCreatedTime(): int;

}
