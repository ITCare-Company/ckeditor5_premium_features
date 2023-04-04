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
  public function getCollaborationPermissionArray(AccountInterface $user, string $filterFormat):array {
    $filterFormatPermission = $this->filterFormatPermission($filterFormat);
    $collaborationPermissions = [];
    if ($user->hasPermission(
      $filterFormatPermission . CollaborationPermissions::COMMENTS_ADMIN)) {
      $collaborationPermissions[] = 'comment:admin';
    }
    elseif ($user->hasPermission(
      $filterFormatPermission . CollaborationPermissions::COMMENTS_WRITE)) {
      $collaborationPermissions[] = 'comment:write';
    }

    if ($user->hasPermission(
      $filterFormatPermission . CollaborationPermissions::DOCUMENT_WRITE)) {
      $collaborationPermissions[] = 'document:write';
      $collaborationPermissions[] = 'document:admin';
    }
    elseif ($user->hasPermission(
      $filterFormatPermission . CollaborationPermissions::DOCUMENT_SUGGESTIONS)) {
      $collaborationPermissions[] = 'document:write';
    }

    return $collaborationPermissions;
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
      $permissions[$formatId] = $this->getCollaborationPermissionArray($user, $formatId);
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
  public function filterFormatPermission(string $filterFormat): string {
    return 'use text format ' . $filterFormat . ' with collaboration ';
  }

}
