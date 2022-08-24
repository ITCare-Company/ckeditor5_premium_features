<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features_notifications\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class for accessing notification config values.
 */
class NotificationSettings {

  private ImmutableConfig $notificationSettings;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->notificationSettings = $configFactory->get(SettingsForm::NOTIFICATION_CONFIG);
  }

  public function getMessageSubject($messageType): string {
    return $this->notificationSettings->get($messageType . '__subject');
  }

  public function getMessageBody($messageType): string {
    return $this->notificationSettings->get($messageType . '__message')['value'];
  }

  public function isMessageEnabled($messageType): bool {
    return (bool) $this->notificationSettings->get($messageType . '__enabled');
  }

  public function getMessageFactoryPluginId(): string {
    return $this->notificationSettings->get('message_factory_plugin');
  }

  public function getSenderPluginId(): string {
    return $this->notificationSettings->get('sender_plugin');
  }

}
