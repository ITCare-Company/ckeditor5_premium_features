<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Entity\Comment;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Comment notification subscriber class.
 */
class NotificationCommentSubscriber implements EventSubscriberInterface {

  /**
   * Event subscriber constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender $notificationSender
   *   Notification sender service.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators $collaboratorsService
   *   Collaborators utility service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user.
   */
  public function __construct(
    protected NotificationSender $notificationSender,
    protected Collaborators $collaboratorsService,
    protected AccountInterface $currentUser,
  ) { }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      CollaborationEventBase::COMMENT_ADDED => 'commentAdded',
    ];
  }

  /**
   * Sends notifications.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $event
   *   Suggestion event object.
   */
  public function commentAdded(CollaborationEventBase $event): void {
    $collaborationEntity = $event->getRelatedEntity();
    if (!$collaborationEntity instanceof Comment) {
      return;
    }

    $mentions = $this->collaboratorsService->getCommentMentions($collaborationEntity);
    if (!empty($mentions)) {
      $users = $this->collaboratorsService->getUserIdsByNames($mentions);
      $this->notificationSender->sendNotification(
        NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_COMMENT,
        $users,
        $event
      );
    }

    $participators = $this->collaboratorsService->getParticipators($collaborationEntity);
    $threadSuggestionAuthor = $this->collaboratorsService->getThreadSuggestionAuthor($collaborationEntity);
    $participators = array_diff($participators, [$threadSuggestionAuthor]);

    if (!$collaborationEntity->isReply()) {
      $authors = $event->getRelatedDocumentAuthors();
      $replyRecipients = array_merge($participators, $authors);

      if (empty($replyRecipients)) {
        return;
      }

      // Send notification to the document author.
      $this->notificationSender->sendNotification(
        NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED,
        $replyRecipients,
        $event
      );
    }

    if (!empty($participators)) {
      // Send notification to users participated in a thread.
      $this->notificationSender->sendNotification(
        NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_THREAD_REPLY,
        $participators,
        $event
      );
    }

    if ($threadSuggestionAuthor > 0) {
      // Send notification to the suggestion author.
      $this->notificationSender->sendNotification(
        NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_REPLY,
        [$threadSuggestionAuthor],
        $event
      );
    }
  }

}
