<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\user\UserInterface;

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
   * @param $messageContent
   *   Content of the document.
   * @param $uid
   *   ID of the message item author.
   * @param $key
   *   ID of the field with related document.
   * @param , $refUid
   *   ID of optionally referenced user.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed
   */
  public function appendItem($itemEntityType, $itemEntityId, $messageType, $eventType, $messageContent, $uid, $key, $refUid): int;

  /**
   * Returns related message items.
   *
   * @return \Drupal\ckeditor5_premium_features_notifications\Entity\MessageItemInterface[]
   */
  public function getItems();

  /**
   * Returns message recipient.
   *
   * @return \Drupal\user\UserInterface|null
   */
  public function getUser(): ?UserInterface;

  /**
   * Returns message title.
   */
  public function getTitle(): string;
}
