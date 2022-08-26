<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

/**
 * Interface for notification_sender plugins.
 */
interface NotificationSenderInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Sends notifications message to specified users.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageInterface $message
   *   Mssage to be sent.
   * @param array $userIds
   *   List of recipients.
   *
   * @return bool|array
   *   Notification sending result. FALSE if not send, ARRAY otherwise.
   */
  public function send(NotificationMessageInterface $message, array $userIds): bool|array;

}
