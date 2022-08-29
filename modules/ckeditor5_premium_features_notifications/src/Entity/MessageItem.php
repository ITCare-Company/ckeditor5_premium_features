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

  public const ENTITY_TYPE_ID = 'ckeditor5_message_item';

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = [];

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Message Item ID'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['message_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Message ID'))
      ->setSetting('target_type', 'ckeditor5_message')
      ->setRequired(TRUE);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setRequired(TRUE)
      ->setSetting('machine_name', TRUE)
      ->setDescription(t('The target entity type.'));

    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The Entity ID.'));

    $fields['message_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
//      ->setRequired(TRUE)
//      ->setDescription(t('The target entity type.'))
    ;

    $fields['event_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
//      ->setRequired(TRUE)
//      ->setDescription(t('The target entity type.'))
    ;

    // @todo: add additional fields with references to required entities - to be able to render data for a user

    return $fields;
  }

}
