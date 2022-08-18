<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\Core\Plugin\PluginBase;

/**
 * Default class used for notification_senders plugins.
 */
abstract class NotificationSenderBase extends PluginBase implements NotificationSenderInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // The title from YAML file discovery may be a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
