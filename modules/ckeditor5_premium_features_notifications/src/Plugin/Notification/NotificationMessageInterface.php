<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;

/**
 * Defines interface for notification message objects.
 */
interface NotificationMessageInterface {

  /**
   * Returns type of the message.
   */
  public function getType(): string;

  /**
   * Returns the title of the message.
   */
  public function getMessageTitle(): string;

  /**
   * Returns the message body array.
   *
   * @return string[]
   */
  public function getMessageBody(): array;

  /**
   * @return \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase
   */
  public function getSourceEvent(): CollaborationEventBase;
}
