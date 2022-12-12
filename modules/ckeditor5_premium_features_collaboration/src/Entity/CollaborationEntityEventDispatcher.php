<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

interface CollaborationEntityEventDispatcher {
  public function dispatchNewEntity(CollaborationEntityInterface $entity);

  public function dispatchUpdatedEntity(CollaborationEntityInterface $oldEntity, CollaborationEntityInterface $newEntity);
}
