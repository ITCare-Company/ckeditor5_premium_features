<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Entity\Suggestion;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Suggestion notification subscriber class.
 */
class NotificationSuggestionSubscriber implements EventSubscriberInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender $notificationSender
   *   Notification sender service.
   */
  public function __construct(
    protected NotificationSender $notificationSender,
    protected AccountInterface $currentUser
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      CollaborationEventBase::SUGGESTION_ACCEPT => 'suggestionStatusChange',
      CollaborationEventBase::SUGGESTION_DISCARD => 'suggestionStatusChange',
      CollaborationEventBase::SUGGESTION_ADDED => 'suggestionAdd',
    ];
  }

  /**
   * Sends notifications.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $event
   *   Suggestion event object.
   */
  public function suggestionStatusChange(CollaborationEventBase $event): void {
    $collaborationEntity = $event->getRelatedEntity();
    if (!$collaborationEntity instanceof Suggestion) {
      return;
    }

    if ($collaborationEntity->getAuthorId() == $event->getAccount()->id()) {
      return;
    }

    if ($collaborationEntity->isInChain() && !$collaborationEntity->isHeadOfChain()) {
      return;
    }

    $recipients = [
      $collaborationEntity->getAuthorId(),
    ];

    $this->notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_STATUS,
      $recipients,
      $event
    );
  }

  /**
   * Sends notifications.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $event
   *   Suggestion event object.
   */
  public function suggestionAdd(CollaborationEventBase $event): void {
    $collaborationEntity = $event->getRelatedDocument();
    if (!$collaborationEntity) {
      return;
    }

    $recipients = $event->getRelatedDocumentAuthors();

    /** @var Suggestion $suggestion */
    $suggestion = $event->getRelatedEntity();
    $suggestionAuthor = $suggestion->getAuthorId();

    // There are cases, when an existing suggestion is split by another user.
    if ($suggestionAuthor != $this->currentUser->id()) {
      return;
    }

    $recipients = array_diff($recipients, [$suggestionAuthor]);

    if (empty($recipients)) {
      return;
    }
    $this->notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_ADDED,
      $recipients,
      $event
    );
  }

}
