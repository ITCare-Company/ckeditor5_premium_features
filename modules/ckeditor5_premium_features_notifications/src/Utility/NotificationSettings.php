<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features_notifications\Form\SettingsForm;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryPluginManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class for accessing notification config values.
 */
class NotificationSettings {

  private ImmutableConfig $notificationSettings;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryPluginManager $messageFactoryPluginManager
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              protected NotificationMessageFactoryPluginManager $messageFactoryPluginManager) {
    $this->notificationSettings = $configFactory->get(SettingsForm::NOTIFICATION_CONFIG);
  }

  /**
   * Returns message subject for specified notification type.
   *
   * @param $messageType
   *   Type of message.
   */
  public function getMessageSubject($messageType): string {
    return $this->notificationSettings->get($messageType . '__subject');
  }

  /**
   * Returns message body for specified notification type.
   *
   * @param $messageType
   *   Type of message.
   */
  public function getMessageBody($messageType): string {
    return $this->notificationSettings->get($messageType . '__message')['value'];
  }

  /**
   * Returns TRUE if specified message type is enabled.
   *
   * @param $messageType
   *   Type of message.
   */
  public function isMessageEnabled($messageType): bool {
    return (bool) $this->notificationSettings->get($messageType . '__enabled');
  }

  /**
   * Returns selected message factory plugin ID.
   */
  public function getMessageFactoryPluginId(): string {
    return $this->notificationSettings->get('message_factory_plugin');
  }

  /**
   * @return \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface|null
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getMessageFactoryPlugin(): ?NotificationMessageFactoryInterface {
    $pluginId = $this->getMessageFactoryPluginId();
    if (!$this->messageFactoryPluginManager->hasDefinition($pluginId)) {
      return NULL;
    }

    return $this->messageFactoryPluginManager->createInstance($pluginId);
  }

  /**
   * Returns selected message sender plugin ID.
   */
  public function getSenderPluginId(): string {
    return $this->notificationSettings->get('sender_plugin');
  }

  /**
   * Return the bulk notifications interval setting.
   */
  public function getBulkNotificationsInterval(): int {
    return $this->notificationSettings->get('sender_bulk_interval') ?? 0;
  }
}
