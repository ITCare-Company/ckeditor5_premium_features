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

  public function send(NotificationMessageInterface $message, array $userIds) :bool|array;

}
