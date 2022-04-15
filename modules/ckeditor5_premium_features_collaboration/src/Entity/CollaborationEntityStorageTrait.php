<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the methods used reused by the collaboration entitites.
 *
 * @todo May be a parent class in the future.
 */
trait CollaborationEntityStorageTrait {

  /**
   * Loads the entities by the parent/context entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The context entity.
   *
   * @return array|\Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[]
   *   The entities matches the given entity.
   */
  public function loadByEntity(EntityInterface $entity): array {
    if (!$entity->id()) {
      return [];
    }

    $entities = $this->loadByProperties([
      'entity_id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ]);

    return array_filter($entities, fn($item) => $item->access('view'));
  }

}
