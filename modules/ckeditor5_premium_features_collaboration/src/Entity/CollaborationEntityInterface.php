<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\user\UserInterface;

/**
 * Provides the interface shared by the all CKEditor5 collaboration entities.
 */
interface CollaborationEntityInterface {

  /**
   * Normalize the entity data from and to the CKEditor API data format.
   *
   * @param array $data
   *   The data.
   * @param bool $reversed
   *   The flag if the data keys should be normalized to the entity keys.
   *
   * @return array
   *   The normalized data.
   */
  public static function normalize(array $data, bool $reversed = FALSE): array;

  /**
   * Gets the normalization mapping.
   *
   * @param bool $reversed
   *   The flag in case of the normalization to the entity keys.
   *
   * @return array
   *   The mapping.
   */
  public static function getNormalizationMapping(bool $reversed): array;

  /**
   * Gets the suggestion author ID.
   *
   * @return int|null
   *   The author ID.
   */
  public function getAuthorId(): ?int;

  /**
   * Gets the entity author.
   *
   * @return \Drupal\user\UserInterface|null
   *   The user.
   */
  public function getAuthor(): ?UserInterface;

  /**
   * Gets the node creation timestamp.
   *
   * @return int
   *   Creation timestamp of the node.
   */
  public function getCreatedTime(): int;

  /**
   * Gets the target entity type ID.
   *
   * @return string
   *   The ID of the entity type.
   */
  public function getEntityTypeTargetId(): string;

  /**
   * Sets the entity type.
   *
   * @param string $id
   *   The ID of target entity type.
   */
  public function setEntityTypeTargetId(string $id): static;


  /**
   * Gets the JSON suggestion attributes.
   *
   * @param bool $raw
   *   FALSE to return decoded, TRUE for having
   *   the raw string value.
   *
   * @return string|array
   *   The data decoded or raw.
   */
  public function getAttributes(bool $raw = FALSE): string|array;

  /**
   * Sets the attributes value.
   *
   * @param array|string $data
   *   The data value (decoded or raw)
   */
  public function setAttributes(array|string $data): static;

}
