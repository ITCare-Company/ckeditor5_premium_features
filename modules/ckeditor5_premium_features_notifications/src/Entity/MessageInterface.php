<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

/**
 * Provides the interface for the CKEditor5 "Message" entity.
 */
interface MessageInterface {

  public const ENTITY_TYPE_ID = 'ckeditor5_message';

  /**
   * Stores new message item related to current message.
   *
   * @param $itemEntityType
   *   Type of the message item entity.
   * @param $itemEntityId
   *   ID of the message item entity.
   * @param $messageType
   *   Type of message.
   * @param $eventType
   *   Type of event.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed
   */
  public function appendItem($itemEntityType, $itemEntityId, $messageType, $eventType, $messageContent): int;

  public function getItems($id);
}
