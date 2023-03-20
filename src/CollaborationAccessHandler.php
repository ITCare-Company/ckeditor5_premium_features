<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the filter format entity type.
 *
 * @see \Drupal\filter\Entity\FilterFormat
 */
class CollaborationAccessHandler {

  /**
   * Constructs a new CollaborationAccessHandler instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * Checks if the user has permission to edit the document.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Current user.
   * @param string $filterFormat
   *   Filter format.
   *
   * @return bool
   *   Is user has permission to edit the document.
   */
  public function isPermittedToEditDocument(AccountInterface $user, string $filterFormat): bool {
    $filterFormatPermission = $this->filterFormatPermission($filterFormat);
    if ($user->hasPermission($filterFormatPermission . '_' . CollaborationPermissions::ADMIN) ||
      $user->hasPermission($filterFormatPermission . '_' . CollaborationPermissions::EDIT)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns a collaboration permission name for a given user and filter format.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Current user.
   * @param string $filterFormat
   *   Filter format.
   *
   * @return string
   *   Permission name.
   */
  public function getCollaborationPermission(AccountInterface $user, string $filterFormat):string {
    $filterFormatPermission = $this->filterFormatPermission($filterFormat);
    if ($user->hasPermission(
      $filterFormatPermission . '_' . CollaborationPermissions::ADMIN)) {
      return CollaborationPermissions::ADMIN;
    }

    if ($user->hasPermission(
      $filterFormatPermission . '_' . CollaborationPermissions::EDIT)) {
      return CollaborationPermissions::EDIT;
    }

    if ($user->hasPermission(
      $filterFormatPermission . '_' . CollaborationPermissions::SUGGESTIONS_ONLY)) {
      return CollaborationPermissions::SUGGESTIONS_ONLY;
    }

    if ($user->hasPermission(
      $filterFormatPermission . '_' . CollaborationPermissions::COMMENTS_ONLY)) {
      return CollaborationPermissions::COMMENTS_ONLY;
    }

    return CollaborationPermissions::READ_ONLY;
  }

  /**
   * Returns array with text formats and permissions for the user.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Current user.
   *
   * @return array
   *   Permissions for the all text formats.
   */
  public function getUserPermissionsForTextFormats(AccountInterface $user): array {
    $formats = $this->entityTypeManager->getStorage('filter_format')->loadByProperties(['status' => TRUE]);
    $permissions = [];
    foreach ($formats as $format) {
      $formatId = $format->id();
      $permissions[$formatId] = $this->getCollaborationPermission($user, $formatId);
    }
    return $permissions;
  }

  /**
   * Returns use permission name for provided filter format.
   *
   * @param string $filterFormat
   *   Filter format name.
   *
   * @return string
   *   Permission name.
   */
  private function filterFormatPermission(string $filterFormat): string {
    return 'use text format ' . $filterFormat;
  }

}
