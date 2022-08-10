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
   * @param $item_key_filter
   *   Key attribute that is used to filter results with different values.
   *
   * @return array|\Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[]
   *   The entities matching the given entity.
   */
  public function loadByEntity(EntityInterface $entity, $item_key_filter = NULL): array {
    if (!$entity->id()) {
      return [];
    }

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityBase[] $entities */
    $entities = $this->loadByProperties([
      'entity_id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ]);

    return array_filter($entities, function ($item) use ($item_key_filter) {
      $attributes = $item->getAttributes();
      return $item->access('view') && ($item_key_filter == NULL || $attributes['key'] == $item_key_filter);
    });
  }

}
