<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides the interface for the CKEditor5 "Suggestion" entity.
 */
interface SuggestionInterface extends ContentEntityInterface {

  public const ENTITY_TYPE_ID = 'ckeditor5_suggestion';

  /**
   * Gets the suggestion type.
   *
   * @return string
   *   The type, defaults to empty string.
   */
  public function getType(): string;

  /**
   * Sets the suggestion type.
   *
   * @param string $type
   *   The type to be set.
   */
  public function setType(string $type): static;

  /**
   * Gets the JSON suggestion data.
   *
   * @param bool $raw
   *   FALSE to return decoded, TRUE for having
   *   the raw string value.
   *
   * @return string|array
   *   The data decoded or raw.
   */
  public function getData(bool $raw = FALSE): string|array;

  /**
   * Sets the data value.
   *
   * @param array|string $data
   *   The data value (decoded or raw).
   */
  public function setData(array|string $data): static;

  /**
   * Sets the comment state (if has comments).
   *
   * @param bool $state
   *   The state value.
   */
  public function setCommentState(bool $state): static;

  /**
   * Gets the has_comments flag value.
   *
   * @return bool
   *   TRUE if has comments, FALSE otherwise.
   */
  public function hasComments(): bool;

}
