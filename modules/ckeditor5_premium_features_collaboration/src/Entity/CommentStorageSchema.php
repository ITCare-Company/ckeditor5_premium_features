<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\ckeditor5_premium_features\Entity\CollaborationStorageSchema;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Defines the message schema handler.
 */
class CommentStorageSchema extends CollaborationStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    if ($data_table = $this->storage->getBaseTable()) {
      $schema[$data_table]['indexes'] += [
        'suggestion__thread' => ['thread_id'],
      ];
    }

    return $schema;
  }
}
