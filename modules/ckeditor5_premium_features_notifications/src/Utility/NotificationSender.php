<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryPluginManager;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderPluginManager;
use Drupal\Core\Database\Connection;

class NotificationSender {

  const NOTIFICATION_OPT_OUT_FIELD_TABLE = 'user__field_ck5_premium_notifications';
  const NOTIFICATION_OPT_OUT_FIELD_VALUE = 'field_ck5_premium_notifications_value';

  public function __construct(protected Connection $dbConnection,
                              protected NotificationSettings $notificationSettings,
                              protected NotificationSenderPluginManager $senderPluginManager,
                              protected NotificationMessageFactoryPluginManager $messageFactoryPluginManager
  ) {}

  /**
   * Sends notification mail.
   *
   * @param array $recipientIds
   * @param array $parameters
   *
   * @return bool|array
   */
  public function sendNotification(string $messageType, array $recipientIds, array $parameters): bool|array {
    if (!$this->notificationSettings->isMessageEnabled($messageType)) {
      return FALSE;
    }

    $recipientIds = $this->filterRecipients($recipientIds);

    if (empty($recipientIds)) {
      return FALSE;
    }

    /** @var \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface $messageFactory */
    $messageFactory = $this->getMessageFactoryPlugin();
    if (!$messageFactory) {
      return FALSE;
    }

    /** @var \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderInterface $sender */
    $sender = $this->getMessageSenderPlugin();
    if (!$sender) {
      return FALSE;
    }

    $message = $messageFactory->getMessage($messageType, $parameters);

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

  /**
   * Returns notification message factory plugin instance.
   */
  protected function getMessageFactoryPlugin(): NotificationMessageFactoryInterface|NULL {
    $pluginId = $this->notificationSettings->getMessageFactoryPluginId();
    if (!$this->messageFactoryPluginManager->hasDefinition($pluginId)) {
      return NULL;
    }

    return $this->messageFactoryPluginManager->createInstance($pluginId);
  }

  /**
   * Returns notification sender plugin instance.
   */
  protected function getMessageSenderPlugin(): NotificationSenderInterface|NULL {
    $pluginId = $this->notificationSettings->getSenderPluginId();
    if (!$this->senderPluginManager->hasDefinition($pluginId)) {
      return NULL;
    }

    return $this->senderPluginManager->createInstance($pluginId);
  }

}
