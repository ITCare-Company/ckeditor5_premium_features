<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Entity\Comment;
use Drupal\ckeditor5_premium_features_collaboration\Entity\Message;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Event\SuggestionEvent;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Comment notification subscriber class.
 */
class NotificationCommentSubscriber implements EventSubscriberInterface {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface|\Drupal\Core\Logger\LoggerChannel
   */
  protected LoggerChannelInterface $loggerChannel;

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender $notificationSender
   *   Notification sender service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $channelFactory
   *   Logger factory.
   */
  public function __construct(
    protected NotificationSender $notificationSender,
    protected Collaborators $collaboratorsService,
    LoggerChannelFactory $channelFactory,
  ) {
    $this->loggerChannel = $channelFactory->get('notifications');
  }

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
    if (!$collaborationEntity instanceof Comment || !$collaborationEntity->isReply()) {
      return;
    }

    $participators = $this->collaboratorsService->getParticipators($collaborationEntity);
    $threadSuggestionAuthor = $this->collaboratorsService->getThreadSuggestionAuthor($collaborationEntity);

    $participators = array_diff($participators, [$threadSuggestionAuthor]);

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
