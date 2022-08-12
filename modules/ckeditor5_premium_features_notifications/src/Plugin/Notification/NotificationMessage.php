<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

class NotificationMessage implements NotificationMessageInterface {

  public function __construct(
    protected string $type,
    protected string $subject,
    protected string $body,
  ) { }

  public function getMessageTitle(): string {
    return $this->subject;
  }

  public function getMessageBody(): array {
    return [$this->body];
  }

  public function getType(): string {
    return $this->type;
  }

}
