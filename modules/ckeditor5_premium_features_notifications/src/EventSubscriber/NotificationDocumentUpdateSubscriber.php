<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Comment notification subscriber class.
 */
class NotificationDocumentUpdateSubscriber implements EventSubscriberInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender $notificationSender
   *   Notification sender service.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators $collaboratorsService
   *   Collaborators service.
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
    $body = $event->getRelatedDocumentContent();
    $mentions = $this->collaboratorsService->getBodyMentions($body);
    if (!empty($mentions)) {
      $users = $this->collaboratorsService->getUserIdsByNames($mentions);
      $users = array_diff($users, [$this->currentUser->id()]);
      if (!empty($users)) {
        $this->notificationSender->sendNotification(
          NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT,
          $users,
          $event
        );
      }
    }
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
