<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryPluginManager;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderPluginManager;
use Drupal\Core\Database\Connection;

class NotificationSender {

  const NOTIFICATION_OPT_OUT_FIELD_TABLE = 'user__field_ck5_premium_notifications';
  const NOTIFICATION_OPT_OUT_FIELD_VALUE = 'field_ck5_premium_notifications_value';

  public function __construct(protected Connection $dbConnection,
                              protected NotificationSenderPluginManager $senderPluginManager,
                              protected NotificationMessageFactoryPluginManager $messageFactoryPluginManager) { }

  /**
   * Sends notification mail.
   *
   * @param array $recipientIds
   * @param array $parameters
   *
   * @return bool|array
   */
  public function sendNotification(string $messageType, array $recipientIds, array $parameters): bool|array {
    $recipientIds = $this->filterRecipients($recipientIds);

    if (empty($recipientIds)) {
      return FALSE;
    }

    // TODO: add configuration to select messge factory plugin id.
    /** @var \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface $messageFactory */
    $messageFactory = $this->messageFactoryPluginManager->createInstance('ck5_notifications_message');

    $message = $messageFactory->getMessage($messageType, $parameters);

    // TODO: add configuration to select sender plugin id.
    /** @var \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderInterface $sender */
    $sender = $this->senderPluginManager->createInstance('ck5_notifications_email');

    return $sender->send($message, $recipientIds);
  }

  /**
   * Returns a list of user ids with notification consent.
   *
   * @param array $userIds
   *
   * @return array
   */
  protected function filterRecipients(array $userIds): array {
    if (empty($userIds)) {
      return [];
    }

    if (!$this->dbConnection->schema()->tableExists(self::NOTIFICATION_OPT_OUT_FIELD_TABLE)) {
      return $userIds;
    }

    return $this->dbConnection->select(self::NOTIFICATION_OPT_OUT_FIELD_TABLE, 'n')
      ->fields('n', ['entity_id'])
      ->condition('entity_id', $userIds, 'IN')
      ->condition(self::NOTIFICATION_OPT_OUT_FIELD_VALUE, 1)
      ->execute()
      ->fetchCol();
  }
}
