<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\Message;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\MessageStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Class for collecting collaborators data.
 */
class Collaborators {

  /**
   * The "suggestion" storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage
   */
  protected SuggestionStorage $suggestionStorage;

  /**
   * The "comments" storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\MessageStorage
   */
  protected MessageStorage $commentsStorage;

  /**
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings $collaborationSettings
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(protected Connection $connection,
                              protected EntityTypeManagerInterface $entityTypeManager,
                              protected CollaborationSettings $collaborationSettings
  ) {
    $this->suggestionStorage = $this->entityTypeManager->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
    $this->commentsStorage = $this->entityTypeManager->getStorage(CommentInterface::ENTITY_TYPE_ID);
  }

  /**
   * Returns a list of user ids that collaborated on specified entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Entity to get collaborators for.
   * @param int $userIdExclude
   *   ID of a user that should be excluded from the list of collaborators.
   *
   * @return array
   *   A list of collaborators id.
   */
  public function getCollaborators(FieldableEntityInterface $entity, int $userIdExclude = 0): array {
    $suggestionAuthors = $this->getEntityCollaboratorType(SuggestionInterface::ENTITY_TYPE_ID, $entity->id(), $entity->getEntityTypeId(), $userIdExclude);
    $revisionAuthors = $this->getEntityCollaboratorType(RevisionInterface::ENTITY_TYPE_ID, $entity->id(), $entity->getEntityTypeId(), $userIdExclude);
    $commentAuthors = $this->getEntityCollaboratorType(CommentInterface::ENTITY_TYPE_ID, $entity->id(), $entity->getEntityTypeId(), $userIdExclude);

    if (empty($suggestionAuthors) && empty($revisionAuthors) && empty($commentAuthors)) {
      return [];
    }

    return array_unique(array_merge($suggestionAuthors, $revisionAuthors, $commentAuthors));
  }

  /**
   * Returns comment thread participators IDs.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $comment
   *   Comment to be checked.
   */
  public function getParticipators(CollaborationEntityInterface $comment): array {
    $commentsInThread = $this->getCommentsThread($comment->getThreadId());

    if (empty($commentsInThread)) {
      return [];
    }

    $collaboratorIds = [];
    $mentionedUsers = [];

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $threadComment */
    foreach ($commentsInThread as $threadComment) {
      if ($threadComment->id() == $comment->id()) {
        continue;
      }
      if ($mentionedInComment = $this->getCommentMentions($threadComment)) {
        $mentionedUsers = array_merge($mentionedUsers, $mentionedInComment);
      }
      if ($threadComment->getAuthorId() == $comment->getAuthorId()) {
        continue;
      }

      $collaboratorIds[] = $threadComment->getAuthorId();
    }

    if (!empty($mentionedUsers)) {
      $collaboratorIds = array_merge($collaboratorIds, $this->getUserIdsByNames($mentionedUsers));
    }

    $collaboratorIds = array_diff($collaboratorIds, [$comment->getAuthorId()]);

    return array_unique($collaboratorIds);
  }

  /**
   * Checks if the comment is a reply to a suggestion and returns the suggestion author ID.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\Message $comment
   *   Comment to be checked.
   *
   * @return int|null
   *   Returns author ID or NULL if no suggestion matches the comment.
   */
  public function getThreadSuggestionAuthor(Message $comment): int|NULL {
    // Get thread.
    $commentsInThread = $this->getCommentsThread($comment->getThreadId());

    if (!$this->isSuggestionThread($commentsInThread)) {
      return NULL;
    }

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $suggestion */
    try {
      $suggestion = $this->suggestionStorage->load($comment->getThreadId());

      if ($suggestion->getAuthorId() != $comment->getAuthorId()) {
        return $suggestion->getAuthorId();
      }
    }
    catch (\Exception $e) {
    }

    return NULL;
  }

  /**
   * Returns a list of comments matched by the thread ID.
   *
   * @param string $threadId
   *   ID of th thread.
   */
  protected function getCommentsThread(string $threadId): array {
    try {
      return $this->commentsStorage->loadByProperties([
        'thread_id' => $threadId,
      ]);
    }
    catch (\Exception $e) {
    }

    return [];
  }

  /**
   * Returns author ids for specified collaboration entity type.
   *
   * @param string $collaborationEntityType
   *   Collaboration entity type.
   * @param int $entityId
   *   Referenced entity.
   * @param string $entityTypeId
   *   Referenced entity type id.
   * @param int $userIdExclude
   *   Author id to be excluded from results.
   *
   * @return array
   *   A list of author ids.
   */
  protected function getEntityCollaboratorType(string $collaborationEntityType, int $entityId, string $entityTypeId, int $userIdExclude = 0): array {
    $query = $this->connection->select($collaborationEntityType, 'd')
      ->fields('d', ['uid'])
      ->condition('entity_id', $entityId)
      ->condition('entity_type', $entityTypeId);

    if ($userIdExclude > 0) {
      $query->condition('uid', $userIdExclude, '!=');
    }

    return $query->execute()->fetchCol();
  }

  /**
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface[] $comments
   */
  protected function isSuggestionThread(array $comments): bool {
    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Message $threadComment */
    foreach ($comments as $threadComment) {
      if ($threadComment->getPosition() == 0 && $threadComment->isReply()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Returns a list of users names mentioned in the comment.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface $comment
   *   Comment object.
   */
  protected function getCommentMentions(CommentInterface $comment): array {
    $marker = $this->collaborationSettings->getMentionsMarker();
    $minCharCount = $this->collaborationSettings->getMentionMinimalCharactersCount();

    $commentBody = $comment->getContentPlain();

    $regexp = '/(^|\s)' . $marker . '([^\s' . $marker . ']{' . $minCharCount . ',})/';

    if (preg_match_all($regexp, $commentBody, $matches)) {
      return $matches[2];
    }

    return [];
  }

  /**
   * Returns a list of IDs for specified usernames.
   *
   * @param array $userNames
   *   List of usernames.
   */
  protected function getUserIdsByNames(array $userNames): array {
    try {
      return $this->entityTypeManager->getStorage('user')
        ->getQuery()
        ->accessCheck(TRUE)
        ->condition('name', $userNames, 'IN')
        ->execute();
    }
    catch (\Exception $e) {
      return [];
    }
  }

}
