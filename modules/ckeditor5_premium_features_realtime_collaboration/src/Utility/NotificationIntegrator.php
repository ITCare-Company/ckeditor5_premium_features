<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Utility;

use Drupal\ckeditor5_premium_features\CKeditorDateFormatterTrait;
use Drupal\ckeditor5_premium_features\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features\Utility\ApiAdapter;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\RtcCommentNotificationEntity;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\RtcNotificationEntityInterface;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\RtcSuggestionNotificationEntity;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides logic for notifications in rtc module.
 */
class NotificationIntegrator {

  use CKeditorDateFormatterTrait;

  /**
   * User storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $userStorage;

  /**
   * NotificationIntegrator constructor.
   *
   * @param \Drupal\ckeditor5_premium_features\Utility\ApiAdapter $apiAdapter
   *   Api adapter.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $eventDispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(protected ApiAdapter $apiAdapter,
                              protected AccountProxyInterface $currentUser,
                              protected ContainerAwareEventDispatcher $eventDispatcher,
                              EntityTypeManagerInterface $entityTypeManager) {
    $this->userStorage = $entityTypeManager->getStorage('user');
  }

  /**
   * Dispatches document update event.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Source entity.
   * @param NotificationDocumentHelper $documentHelper
   *   Notification Document helper.
   */
  public function handleDocumentUpdateEvent(FieldableEntityInterface $entity,
                                            NotificationDocumentHelper $documentHelper): void {
    $this->dispatchEvent($entity, CollaborationEventBase::DOCUMENT_UPDATED, $documentHelper);
  }

  /**
   * Prepare and send suggestions evens.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Related entity.
   * @param NotificationDocumentHelper $documentHelper
   *   Notification document helper.
   * @param string $changeDate
   *   Last date of change.
   * @param array $suggestions
   *   Array of suggestions.
   * @param array $commentsThreads
   *   Array of comments threads.
   */
  public function handleSuggestionsEvent(FieldableEntityInterface $entity,
                                         NotificationDocumentHelper $documentHelper,
                                         string $changeDate,
                                         array $suggestions,
                                         array $commentsThreads): void {
    if (empty($suggestions)) {
      return;
    }
    $newSuggestions = array_filter($suggestions, function ($suggestion) use ($changeDate) {
      if (empty($suggestion['updated_at'])) {
        return FALSE;
      }
      return strtotime($suggestion['updated_at']) > $changeDate;
    });
    foreach ($newSuggestions as $key => $suggestion) {
      $newSuggestions[$key]['thread'] = $commentsThreads[$key] ?? [];
    }
    foreach ($newSuggestions as $suggestion) {
      $this->dispatchSuggestionEvent($suggestion, $entity, $documentHelper);
    }
  }

  /**
   * Prepare suggestion event object.
   *
   * @param array $suggestion
   *   The suggestion.
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Related entity.
   * @param NotificationDocumentHelper $documentHelper
   *   Notification document helper.
   */
  public function dispatchSuggestionEvent(array $suggestion,
                                          FieldableEntityInterface $entity,
                                          NotificationDocumentHelper $documentHelper): void {
    $rtcSuggestion = new RtcSuggestionNotificationEntity();
    $thread = [];
    $author = $this->userStorage->load($suggestion['author_id']);
    if (!empty($suggestion['thread']['comments'])) {
      foreach ($suggestion['thread']['comments'] as $comment) {
        $rtcComment = new RtcCommentNotificationEntity();
        $rtcComment
          ->setId($comment['commentId'])
          ->setContent($comment['content'])
          ->setCreatedDate($this->format(strtotime($comment['createdAt'])))
          ->setAuthor($author);
        $thread[$comment['commentId']] = $rtcComment;
      }
    }
    $rtcSuggestion
      ->setId($suggestion['id'])
      ->setAuthor($author)
      ->setEntityTypeTargetId($entity->getEntityTypeId())
      ->setReferencedEntity($entity)
      ->setChain($suggestion['chain'] ?? [])
      ->setThread($thread)
      ->setThreadId($suggestion['id']);

    switch ($suggestion['state']) {
      case 'accepted':
        $eventType = CollaborationEventBase::SUGGESTION_ACCEPT;
        break;

      case 'rejected':
        $eventType = CollaborationEventBase::SUGGESTION_DISCARD;
        break;

      default:
        $eventType = CollaborationEventBase::SUGGESTION_ADDED;
        break;
    }
    $this->dispatchEvent($rtcSuggestion, $eventType, $documentHelper);
  }

  /**
   * Check if new comment has been added.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Related entity.
   * @param NotificationDocumentHelper $documentHelper
   *   Notification document helper.
   * @param string $changeDate
   *   Last date of change.
   * @param array $commentsThreads
   *   Array of comments threads.
   * @param array $suggestions
   *   Array of suggestions.
   */
  public function handleCommentsEvent(FieldableEntityInterface $entity,
                                      NotificationDocumentHelper $documentHelper,
                                      string $changeDate,
                                      array $commentsThreads,
                                      array $suggestions): void {
    if (empty($commentsThreads)) {
      return;
    }

    $newComments = [];
    foreach ($commentsThreads as $key => $commentThread) {
      if (empty($commentThread['comments'])) {
        continue;
      }
      foreach ($commentThread['comments'] as $comment) {
        if (strtotime($comment['createdAt']) > $changeDate) {
          if (!array_key_exists($key, $newComments)) {
            $newComments[$key] = $commentThread;
          }
          $newComments[$key]['new'][$comment['commentId']] = $comment;
          if (!array_key_exists('isReply', $newComments[$key])) {
            if (count($commentThread['comments']) > 1) {
              $newComments[$key]['isReply'] = TRUE;
            }
            else {
              $newComments[$key]['isReply'] = FALSE;
            }
          }
        }
      }
    }

    foreach ($newComments as $key => $commentThread) {
      $thread = [];
      foreach ($commentThread['comments'] as $comment) {
        $author = $this->userStorage->load($comment['authorId']);
        $rtcComment = new RtcCommentNotificationEntity();
        $rtcComment
          ->setId($comment['commentId'])
          ->setContent($comment['content'])
          ->setCreatedDate($this->format(strtotime($comment['createdAt'])))
          ->setAuthor($author);
        $thread[$comment['commentId']] = $rtcComment;
      }
      $newComment = end($commentThread['new']);
      $rtcComment = $thread[$newComment['commentId']];
      $commentThread['isSuggestionComment'] = empty($commentThread['context']);

      $rtcComment
        ->setIsReply($commentThread['isReply'] ?? FALSE)
        ->setThread($thread)
        ->setThreadId($key)
        ->setReferencedEntity($entity)
        ->setEntityTypeTargetId($entity->getEntityTypeId());

      if ($commentThread['isSuggestionComment']) {
        $suggestion = $suggestions[$key] ?? NULL;
        if ($suggestion) {
          $rtcSuggestion = new RtcSuggestionNotificationEntity();
          $author = $this->userStorage->load($suggestion['author_id']);
          $rtcSuggestion
            ->setId($suggestion['id'])
            ->setAuthor($author)
            ->setEntityTypeTargetId($entity->getEntityTypeId())
            ->setReferencedEntity($entity)
            ->setChain($suggestion['chain'] ?? [])
            ->setThread($thread)
            ->setThreadId($suggestion['id']);

          $rtcComment
            ->setRelatedSuggestion($rtcSuggestion)
            ->setIsSuggestionComment($commentThread['isSuggestionComment'])
            ->setIsReply(TRUE);
        }
      }
      $this->dispatchEvent($rtcComment, CollaborationEventBase::COMMENT_ADDED, $documentHelper);
    }
  }

  /**
   * Dispatch event.
   *
   * @param \Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\RtcNotificationEntityInterface|FieldableEntityInterface $entity
   *   Related entity.
   * @param string $eventType
   *   Event type.
   * @param NotificationDocumentHelper $documentHelper
   *   Notification document helper.
   */
  private function dispatchEvent(RtcNotificationEntityInterface|FieldableEntityInterface $entity,
                                 string $eventType,
                                 NotificationDocumentHelper $documentHelper): void {
    $event = new CollaborationEventBase(
      $entity,
      $this->userStorage->load($this->currentUser->id()),
      $eventType,
    );
    $event->setRelatedDocumentKey($documentHelper->getElementId());
    if (!empty($documentHelper->getOriginalData())) {
      $event->setOriginalContent($documentHelper->getOriginalData());
    }
    if (!empty($documentHelper->getNewData())) {
      $event->setNewContent($documentHelper->getNewData());
    }

    $this->eventDispatcher->dispatch(
      $event,
      $eventType
    );
  }

  /**
   * Create chained suggestions array.
   *
   * @param array $suggestions
   *   Suggestions to be chained.
   *
   * @return array
   *   Array of chained suggestions.
   */
  public function chainSuggestion(array $suggestions): array {
    $chainedSuggestions = [];
    foreach ($suggestions as $suggestion) {
      $head = $suggestion['attributes']['head'] ?? NULL;
      if ($head && $head !== $suggestion['id']) {
        NestedArray::setValue(
          $chainedSuggestions,
          [$head, 'chain', $suggestion['id']],
          $suggestion);
      }
      else {
        $chainedSuggestions[$suggestion['id']] = NestedArray::mergeDeep($suggestion, $chainedSuggestions[$suggestion['id']] ?? []);
        NestedArray::setValue($chainedSuggestions, [
          $suggestion['id'],
          'chain',
          $suggestion['id'],
        ],
          $suggestion);
      }
    }
    return $chainedSuggestions;
  }

  /**
   * Set key value as thread id.
   *
   * @param array $commentsData
   *   Array of comments.
   */
  public function transformCommentsData(array &$commentsData): void {
    foreach ($commentsData as $key => $comment) {
      $commentsData[$comment['threadId']] = $comment;
      unset($commentsData[$key]);
    }
  }

}
