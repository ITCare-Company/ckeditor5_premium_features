<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features\Plugin\Filter\FilterCollaboration;
use Drupal\ckeditor5_premium_features_notifications\Diff\Ckeditor5DiffInterface;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Session\AccountInterface;
use Drupal\filter\FilterPluginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Comment notification subscriber class.
 */
class NotificationDocumentUpdateSubscriber implements EventSubscriberInterface {

  /**
   * Collaboration filter.
   *
   * @var \Drupal\ckeditor5_premium_features\Plugin\Filter\FilterCollaboration
   */
  protected FilterCollaboration $filterCollaboration;

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender $notificationSender
   *   Notification sender service.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators $collaboratorsService
   *   Collaborators service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user.
   * @param \Drupal\ckeditor5_premium_features_notifications\Diff\Ckeditor5DiffInterface $ckeditor5Diff
   *   Ckeditor5 diff service.
   * @param \Drupal\filter\FilterPluginManager $filterPluginManager
   *   Filter plugin manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct(
    protected NotificationSender $notificationSender,
    protected Collaborators $collaboratorsService,
    protected AccountInterface $currentUser,
    protected Ckeditor5DiffInterface $ckeditor5Diff,
    FilterPluginManager $filterPluginManager
  ) {
    $this->filterCollaboration = $filterPluginManager->createInstance('ckeditor5_premium_features_collaboration_filter');
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
    $body = $event->getRelatedDocumentContent();
    $previousBody = $event->getOriginalContent();

    // Let's cleanup the incoming body, to not send notifications
    // about added collaboration tags (track changes, comments)
    $body = $this->filterCollaboration->process($body, NULL);
    $previousBody = $this->filterCollaboration->process($previousBody, NULL);

    if (!empty($previousBody)) {
      $difference = $this->ckeditor5Diff->getDiff($previousBody, $body);

      $changeContext = $this->ckeditor5Diff->getDiffContext();

      if (empty($changeContext)) {
        return;
      }

      $event->setOriginalContent($changeContext);
    }
    else {
      $difference = $body;
    }

    if (empty($body) && empty($difference) && empty($changeContext)) {
      return;
    }

    if (!empty($difference)) {
      $mentions = $this->collaboratorsService->getBodyMentions($difference);
      if (!empty($mentions)) {
        $users = $this->collaboratorsService->getUserIdsByNames($mentions);
        $users = array_diff($users, [$this->currentUser->id()]);
        if (!empty($users)) {
          $mentionEvent = clone $event;
          $mentionEvent->setOriginalContent(
            $this->ckeditor5Diff->getDiffAddedContext()
          );

          $this->notificationSender->sendNotification(
            NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT,
            $users,
            $mentionEvent
          );
        }
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
