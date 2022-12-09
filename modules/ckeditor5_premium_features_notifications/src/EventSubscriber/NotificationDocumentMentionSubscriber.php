<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features\CKeditorPremiumLoggerChannelTrait;
use Drupal\ckeditor5_premium_features\Plugin\Filter\FilterCollaboration;
use Drupal\ckeditor5_premium_features_notifications\Diff\Ckeditor5DiffInterface;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSettings;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Session\AccountInterface;
use Drupal\filter\FilterPluginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Document notification subscriber class for sending mentions notifications.
 */
class NotificationDocumentMentionSubscriber implements EventSubscriberInterface {

  use CKeditorPremiumLoggerChannelTrait;

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
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSettings $notificationSettings
   *   Notifications settings helper.
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
    protected NotificationSettings $notificationSettings,
    FilterPluginManager $filterPluginManager
  ) {
    $this->filterCollaboration = $filterPluginManager->createInstance('ckeditor5_premium_features_collaboration_filter');
  }

  /**
   * Collaboration filter.
   *
   * @var \Drupal\ckeditor5_premium_features\Plugin\Filter\FilterCollaboration
   */
  protected FilterCollaboration $filterCollaboration;

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
    $documentAuthors = $event->getRelatedDocumentAuthors();

    $body = $event->getRelatedDocumentContent();
    $previousBody = $event->getOriginalContent();

    $bodyWithoutCollaborationTags = $this->filterCollaboration->process($body, NULL);
    $previousBodyWithoutCollaborationTags = $this->filterCollaboration->process($previousBody, NULL);

    if (!empty($previousBody)) {
      $difference = $this->ckeditor5Diff->getDiff($previousBody, $body);

      $addedContentContext = $this->ckeditor5Diff->getDiffAddedContext();

      $event->setOriginalContent($addedContentContext);
    }
    else {
      $difference = $body;
    }

    if (!empty($previousBodyWithoutCollaborationTags)) {
      $differenceWithoutSuggestion = $this->ckeditor5Diff->getDiff($previousBodyWithoutCollaborationTags, $bodyWithoutCollaborationTags);

      $addedContentContextWithoutSuggestions = $this->ckeditor5Diff->getDiffAddedContext();
    }

    if (empty($body) || empty($difference)) {
      return;
    }

    // All mentioned users (in basic and collaboration text).
    $mentions = $this->collaboratorsService->getBodyMentions($difference);
    if (empty($mentions)) {
      return;
    }

    $usersMentioned = $this->collaboratorsService->getUserIdsByNames($mentions);
    $usersMentioned = array_diff($usersMentioned, [$this->currentUser->id()]);
    if (empty($usersMentioned)) {
      return;
    }

    // Bellow we're trying to detect mentioned document Authors in order to
    // conditionally filter them from the Mention notification. We need to
    // do this, because otherwise authors will receive the same part of document
    // also in the New Suggestion notification and/or Document Update
    // notification.
    // In other words, we want to send the Mention notification to the authors,
    // only if:
    // 1. Mention occurred in a suggestion, but the New Suggestion notifications
    //    are disabled.
    // 2. Mention occurred in a new content added to the document, but the
    //    Document Update notification is disabled.
    $mentionsOutsideSuggestions = empty($differenceWithoutSuggestion) ? [] : $this->collaboratorsService->getBodyMentions($differenceWithoutSuggestion);

    $usersMentionedOutsideSuggestions = empty($mentionsOutsideSuggestions) ? [] : $this->collaboratorsService->getUserIdsByNames($mentionsOutsideSuggestions);
    $authorsMentionedOutsideSuggestions = array_intersect($usersMentionedOutsideSuggestions, $documentAuthors);
    $authorsMentionedInSuggestions = array_diff(array_intersect($usersMentioned, $documentAuthors), $authorsMentionedOutsideSuggestions);

    if ($this->notificationSettings->isMessageEnabled(NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_ADDED)) {
      $usersMentioned = array_diff($usersMentioned, $authorsMentionedInSuggestions);
    }
    if ($this->notificationSettings->isMessageEnabled(NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_DEFAULT)) {
      $usersMentioned = array_diff($usersMentioned, $authorsMentionedOutsideSuggestions);
    }
    elseif (!empty($authorsMentionedOutsideSuggestions) &&
      $this->notificationSettings->isMessageEnabled(NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_ADDED) &&
      !empty($addedContentContextWithoutSuggestions)) {

      $usersMentioned = array_diff($usersMentioned, $authorsMentionedOutsideSuggestions);
      $authorsFilteredEvent = clone $event;
      $authorsFilteredEvent->setOriginalContent($addedContentContextWithoutSuggestions);

      try {
        $this->notificationSender->sendNotification(
          NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT,
          $authorsMentionedOutsideSuggestions,
          $authorsFilteredEvent
        );
      }
      catch (PluginException $e) {
        $this->logException("Error occurred while sending Mention notification.", $e);
      }
    }

    if (!empty($usersMentioned)) {
      try {
        $this->notificationSender->sendNotification(
          NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT,
          $usersMentioned,
          $event
        );
      }
      catch (PluginException $e) {
        $this->logException("Error occurred while sending Mention notification.", $e);
      }
    }

  }

}
