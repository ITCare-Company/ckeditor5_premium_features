<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;

/**
 * Interface for notification_message plugins.
 */
interface NotificationMessageFactoryInterface {

  const CKEDITOR5_MESSAGE_DEFAULT = 'ckeditor5_message_default';
  const CKEDITOR5_MESSAGE_MENTION_COMMENT = 'ckeditor5_message_mention_comment';
  const CKEDITOR5_MESSAGE_MENTION_DOCUMENT = 'ckeditor5_message_mention_document';
  const CKEDITOR5_MESSAGE_COMMENT_ADDED = 'ckeditor5_message_comment_added';
  const CKEDITOR5_MESSAGE_THREAD_REPLY = 'ckeditor5_message_thread_reply';
  const CKEDITOR5_MESSAGE_SUGGESTION_REPLY = 'ckeditor5_message_suggestion_reply';
  const CKEDITOR5_MESSAGE_SUGGESTION_STATUS = 'ckeditor5_message_suggestion_status';
  const CKEDITOR5_MESSAGE_SUGGESTION_ADDED = 'ckeditor5_message_suggestion_added';

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

  /**
   * Returns message object suitable for specified type.
   *
   * @param string $messageType
   *   Type of the message.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $event
   *   Collaboration event object.
   *
   * @return \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageInterface|NULL
   */
  public function getMessage(string $messageType, CollaborationEventBase $event): NotificationMessageInterface|NULL;

  /**
   * Checks if passed message type is supported by the plugin.
   *
   * @param string $messageType
   *   Type of the message.
   */
  public static function isMessageTypeSupported(string $messageType): bool;
}
