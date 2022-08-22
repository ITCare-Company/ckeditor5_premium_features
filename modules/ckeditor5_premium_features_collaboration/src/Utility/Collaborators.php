<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Render\Element\Html;

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
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage
   */
  protected CommentsStorage $commentsStorage;

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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getParticipators(CollaborationEntityInterface $comment): array {
    $collaboratorIds = [];

    $commentsInThread = $this->commentsStorage->loadByProperties([
      'thread_id' => $comment->getThreadId(),
    ]);

    $mentionedUsers = [];

    /** @var CollaborationEntityInterface $threadComment */
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

    if ($this->isSuggestionThread($commentsInThread)) {
      /** @var CollaborationEntityInterface $suggestion */
      $suggestion = $this->suggestionStorage->load($comment->getThreadId());

      if ($suggestion->getAuthorId() != $comment->getAuthorId()) {
        $collaboratorIds[] = $suggestion->getAuthorId();
      }
    }


    return array_unique($collaboratorIds);
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
   * @param CommentInterface[] $comments
   */
  protected function isSuggestionThread(array $comments): bool {
    $firstComment = reset($comments);
    return $firstComment->isReply();
  }

  /**
   * Returns a list of users names mentioned in the comment.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface $comment
   *   Comment object.
   */
  protected function getCommentMentions(CommentInterface $comment): array{
    $marker = $this->collaborationSettings->getMentionsMarker();
    $minCharCount = $this->collaborationSettings->getMentionMinimalCharactersCount();

    $commentBody = $comment->getContentPlain();

    $regexp = '/(^|\s)' . $marker . '([^\s' . $marker . ']{' . $minCharCount. ',})/';

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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getUserIdsByNames(array $userNames): array {
    return $this->entityTypeManager->getStorage('user')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('name', $userNames, 'IN')
      ->execute();
  }
}
