<?php

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the message schema handler.
 */
class ChannelStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE): array {
    $schema = parent::getEntitySchema($entity_type, $reset);

    if ($data_table = $this->storage->getBaseTable()) {
      $schema[$data_table]['fields']['key_id']['not null'] = TRUE;

      $schema[$data_table]['unique keys'] += [
        'channel_entity' => ['entity_id', 'entity_type', 'key_id'],
      ];
    }

    return $schema;
  }

}
