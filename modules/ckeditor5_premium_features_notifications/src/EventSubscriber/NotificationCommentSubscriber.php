<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Entity\Comment;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
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
    protected StateInterface $state
  ) {}

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

    $notificationSentToUsers = $this->state->get(NotificationMessageFactoryInterface::CKEDITOR5_SUGGESTION_SENT_TO_USERS_STATE_KEY);
    $newSuggestionParticipators = $notificationSentToUsers[$collaborationEntity->getThreadId()] ?? [];

    $participators = $this->collaboratorsService->getParticipators($collaborationEntity);
    $threadSuggestionAuthor = $this->collaboratorsService->getThreadSuggestionAuthor($collaborationEntity);
    if ($threadSuggestionAuthor) {
      $participators[] = $threadSuggestionAuthor;
    }
    $isSuggestionReplay = $this->collaboratorsService->isCommentInSuggestionThread($collaborationEntity);
    $participators = array_unique($participators);
    $replyRecipients = [];
    $authors = $event->getRelatedDocumentAuthors();

    $participators = array_diff($participators, $newSuggestionParticipators);

    if (!$collaborationEntity->isReply()) {
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

    if (!empty($participators) && empty($replyRecipients)) {
      if (!$isSuggestionReplay) {
        // Send notification to users participated in a thread.
        $this->notificationSender->sendNotification(
          NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_THREAD_REPLY,
          $participators,
          $event
        );
      }
      else {
        // Send notification to the suggestion author.
        $this->notificationSender->sendNotification(
          NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_REPLY,
          $participators,
          $event
              );
      }
    }

    $mentions = $this->collaboratorsService->getCommentMentions($collaborationEntity);
    if (!empty($mentions)) {
      $users = $this->collaboratorsService->getUserIdsByNames($mentions);

      if ($isSuggestionReplay && empty($participators)) {
        $users = array_diff($users, $authors);
      }

      $this->checkIfNotificationAlreadySentToUsers(
        $users,
        array_merge($replyRecipients, $participators)
      );
      if (!empty($users)) {
        $this->notificationSender->sendNotification(
          NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_COMMENT,
          $users,
          $event
        );
      }
    }
  }

  /**
   * Check if notification was sent to user.
   *
   * Don't send a notification about a mention to the user
   * if received a notification about the comment
   * where the mention is placed.
   *
   * @param array $users
   *   Array with mentioned users.
   * @param array $recipients
   *   Array with users who already have received notification.
   */
  protected function checkIfNotificationAlreadySentToUsers(array &$users, array $recipients): void {
    if (!empty($recipients)) {
      $users = array_filter($users, fn($x) => !in_array($x, $recipients));
    }
  }

}
