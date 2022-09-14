<?php

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the message schema handler.
 */
class MessageStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    if ($data_table = $this->storage->getBaseTable()) {
      unset($schema[$data_table]['indexes']['ckeditor5_message_field__uid__target_id']);

      $schema[$data_table]['fields']['created']['not null'] = TRUE;
      $schema[$data_table]['fields']['updated']['not null'] = TRUE;
      $schema[$data_table]['fields']['sent']['not null'] = TRUE;

      $schema[$data_table]['indexes'] += [
        'message__sent' => ['sent', 'updated'],
        'message__user' => ['uid', 'entity_type', 'entity_id', 'sent'],
      ];
    }

    return $schema;
  }

}
