<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the collaboration entities storage methods.
 */
interface CollaborationEntityStorageInterface {

  /**
   * Creates the CKEDitor5 collaboration entity.
   *
   * @param array $raw_data
   *   The raw data to be used in the creation.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface
   *   The created entity.
   */
  public function add(array $raw_data): CollaborationEntityInterface;

  /**
   * Updates the CKEDitor5 collaboration entity.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $entity
   *   The suggestion entity.
   * @param array $raw_data
   *   The raw data to be updated.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface
   *   The created entity.
   */
  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface;

  /**
   * Returns a list of collaboration entities attributes generated from collaboration source array.
   *
   * @param array $source_data
   *   Collaboration source array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Related entity.
   * @param string $item_key
   *   Related entity field key.
   */
  public function processSourceData(array $source_data, ContentEntityInterface $entity, string $item_key): array;
}
