<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

/**
 * Used for storing basic information about notification message.
 */
class NotificationMessage implements NotificationMessageInterface {

  /**
   * @param string $type
   *   Type of message.
   * @param string $subject
   *   Message subject.
   * @param string $body
   *   Message body.
   */
  public function __construct(
    protected string $type,
    protected string $subject,
    protected string $body,
  ) { }

  /**
   * {@inheritdoc}
   */
  public function getMessageTitle(): string {
    return $this->subject;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessageBody(): array {
    return [$this->body];
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->type;
  }

}
