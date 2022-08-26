<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityBase;
use Drupal\ckeditor5_premium_features_collaboration\Entity\Suggestion;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Event\SuggestionEvent;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Comment notification subscriber class.
 */
class NotificationDocumentUpdateSubscriber implements EventSubscriberInterface {

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
      CollaborationEventBase::DOCUMENT_UPDATED => 'documentUpdated',
    ];
  }

  /**
   * Sends notifications.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $event
   *   Suggestion event object.
   */
  public function documentUpdated(CollaborationEventBase $event): void {
    $collaborationEntity = $event->getRelatedEntity();

    $otherAuthorsList = $this->collaboratorsService->getCollaborators($collaborationEntity, $event->getAccount()->id());

    if (empty($otherAuthorsList)) {
      return;
    }

    $this->notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_DEFAULT,
      $otherAuthorsList,
      $event
    );
  }

}
