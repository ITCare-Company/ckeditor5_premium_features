<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;

/**
 * Used for storing basic information about notification message.
 */
class NotificationMessage implements NotificationMessageInterface {

  /**
   * Constructor.
   *
   * @param string $type
   *   Type of message.
   * @param string $subject
   *   Message subject.
   * @param string $body
   *   Message body.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $sourceEvent
   *   Collaboration source event.
   */
  public function __construct(
    protected string $type,
    protected string $subject,
    protected string $body,
    protected CollaborationEventBase $sourceEvent
  ) {}

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

  /**
   * {@inheritdoc}
   */
  public function getSourceEvent(): CollaborationEventBase {
    return $this->sourceEvent;
  }

}
