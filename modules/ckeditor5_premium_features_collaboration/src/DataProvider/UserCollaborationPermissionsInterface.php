<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\DataProvider;

use Drupal\Core\Session\AccountInterface;

/**
 * Interface describing a collaboration permissions for the ckeditor5.
 */
interface UserCollaborationPermissionsInterface {

  public const COLLABORATION_PERMISSION_ADMIN = 'collaboration admin';
  public const COLLABORATION_PERMISSION_EDITOR = 'collaboration editor';
  public const COLLABORATION_PERMISSION_SUGGESTIONS_ONLY = 'collaboration suggestions only';
  public const COLLABORATION_PERMISSION_COMMENTS_ONLY = 'collaboration comments only';

  public const CKE5_PERMISSION_ADMIN = 'admin';
  public const CKE5_PERMISSION_EDIT = 'edit';
  public const CKE5_PERMISSION_SUGGESTIONS_ONLY = 'suggestions_only';
  public const CKE5_PERMISSION_COMMENTS_ONLY = 'comments_only';
  public const CKE5_PERMISSION_READ_ONLY = 'read_only';

  /**
   * Creates the data provider instance from the given entities.
   *
   * @param array|\Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[] $entities
   *   The entities related to the user.
   */
  public function getFromEntities(array $entities): array;

  /**
   * Returns a collaboration permission name for the current user.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Current user.
   *
   * @return string
   *   Permission name.
   */
  public function getCollaborationPermission(AccountInterface $user): string;

  /**
   * Checks if the user has permission to edit the document.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Current user.
   *
   * @return bool
   *   Is user has permission to edit the document.
   */
  public function isPermittedToEditDocument(AccountInterface $user): bool;

}
