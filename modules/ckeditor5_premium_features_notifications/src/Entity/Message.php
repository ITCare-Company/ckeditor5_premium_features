<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the CKEditor5 Premium features "Message" entity.
 *
 * @ContentEntityType(
 *   id = "ckeditor5_message",
 *   label = @Translation("CKEditor5 Message"),
 *   base_table = "ckeditor5_message",
 *   entity_keys = {
 *      "id" = "id",
 *      "uid" = "uid",
 *      "entity_type" = "entity_type",
 *      "entity_id" = "entity_id",
 *   },
 *   handlers = {
 *     "storage" = "Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage",
 *     "storage_schema" = "Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorageSchema",
 *   }
 * )
 */
class Message extends ContentEntityBase {

  public const ENTITY_TYPE_ID = 'ckeditor5_message';

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = [];

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Message ID'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setRequired(TRUE)
      ->setSetting('machine_name', TRUE)
      ->setDescription(t('The target entity type.'));

    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The Entity ID.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the message was created.'))
      ->setRequired(TRUE)
      ->setStorageRequired(TRUE);

    $fields['updated'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setRequired(TRUE)
      ->setDescription(t('The time that the message was created.'))
      ->setDefaultValueCallback(static::class . '::getRequestTime')
      ->setStorageRequired(TRUE);

    $fields['sent'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sent'))
      ->setRequired(TRUE)
      ->setDescription(t('A boolean indicating whether this message was sent.'))
      ->setDefaultValue(FALSE);

    return $fields;
  }

  /**
   * @param $itemType
   * @param $itemId
   * @param $messageType
   * @param $eventType
   *
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function appendItem($itemType, $itemId, $messageType, $eventType): int {
    $saveResult = $this->entityTypeManager()->getStorage(MessageItem::ENTITY_TYPE_ID)
      ->create([
        'message_id' => $this->id(),
        'entity_type' => $itemType,
        'entity_id' => $itemId,
        'message_type' => $messageType,
        'event_type' => $eventType,
      ])->save();

    if ($saveResult == SAVED_NEW || $saveResult == SAVED_UPDATED) {
      $this->set('updated', time());
      $this->save();
    }

    return $saveResult;
  }

  /**
   * Returns current request timestamp.
   *
   * @return int
   */
  public static function getRequestTime(): int {
    return \Drupal::time()->getRequestTime();
  }

}
