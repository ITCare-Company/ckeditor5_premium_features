<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Helper class for getting user data.
 */
class UserHelper {

  /**
   * User storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $userStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   File Url generator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected FileUrlGeneratorInterface $fileUrlGenerator,
    protected EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->userStorage = $this->entityTypeManager->getStorage('user');
  }

  /**
   * Returns user data.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   *
   * @return array
   *   Basic information about user.
   */
  public function getUserData(AccountProxyInterface $account): array {
    $data = [
      'name' => $account->getAccountName(),
    ];

    if ($account->getEmail()) {
      $data['email'] = $account->getEmail();
    }

    $user = $this->userStorage->load($account->id());
    if ($user && $user->hasField('user_picture')) {
      $userPicture = $user->get('user_picture');
      $file = $userPicture->entity;
      if ($file) {
        $fileUri = $file->getFileUri();
        $fileFullUrl = $this->fileUrlGenerator->generateAbsoluteString($fileUri);
        if ($fileFullUrl) {
          $data['avatar'] = $fileFullUrl;
        }
      }
    }

    return $data;
  }

}
