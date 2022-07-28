<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\FieldableEntityInterface;

class Collaborators {

  /**
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(protected Connection $connection) {  }

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

}
