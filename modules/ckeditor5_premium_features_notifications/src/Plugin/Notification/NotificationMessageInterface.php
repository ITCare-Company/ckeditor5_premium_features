<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

interface NotificationMessageInterface {

  public function getType(): string;

  public function getMessageTitle(): string;

  public function getMessageBody() :array;

}
