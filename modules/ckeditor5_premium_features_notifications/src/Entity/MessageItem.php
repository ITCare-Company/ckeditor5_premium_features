<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * @ContentEntityType(
 *   id = "ckeditor5_message_item",
 *   label = @Translation("CKEditor5 Message Item"),
 *   base_table = "ckeditor5_message_item",
 *   entity_keys = {
 *      "id" = "id",
 *      "message_id" = "message_id",
 *   },
 *   handlers = {
 *   }
 * )
 */
class MessageItem extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = [];

    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Message Item ID'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['message_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Message ID'))
      ->setSetting('target_type', 'ckeditor5_message')
      ->setRequired(TRUE);

    // @todo: add additional fields with references to required entities - to be able to render data for a user

    return $fields;
  }

}
