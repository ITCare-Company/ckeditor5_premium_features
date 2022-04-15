<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\DataProvider;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;

/**
 * Provides the user data for the editor featuers.
 */
class UserDataProvider {

  /**
   * The image style storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  protected ImageStyleStorageInterface $imageStyleStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  protected UserStorageInterface $userStorage;

  /**
   * Creates the provider instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected AccountProxyInterface $account,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->imageStyleStorage = $entity_type_manager->getStorage('image_style');
  }

  /**
   * Creates the data provider instance from the given entities.
   *
   * @param array|\Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[] $entities
   *   The entities related to the user.
   */
  public function getFromEntities(array $entities): array {
    $users = [];
    foreach ($entities as $entity) {
      $user = $entity->getAuthor();
      $users[$user->id()] = $user;
    }

    if (!array_key_exists($this->account->id(), $users)) {
      $users[] = $this->userStorage->load($this->account->id());
    }

    return $this->getData($users);
  }

  /**
   * Gets the normalized users data.
   *
   * @param array|\Drupal\user\UserInterface[] $users
   *   The user entities.
   *
   * @return array
   *   The normalized data.
   */
  protected function getData(array $users): array {
    $data = [];

    foreach ($users as $user) {
      if (!$user->access('view')) {
        continue;
      }

      $data[$user->id()] = [
        'id' => $user->id(),
        'name' => $user->getDisplayName(),
        'avatar' => $this->getUserPicture($user),
      ];
    }

    return $data;
  }

  /**
   * Gets the user picture URL if defined.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   *
   * @return string|null
   *   URL or null.
   */
  protected function getUserPicture(UserInterface $user): ?string {
    if (!$user->hasField('user_picture')) {
      return NULL;
    }

    /** @var \Drupal\file\Entity\File $image */
    $image = $user->get('user_picture')->entity;
    $picture = NULL;
    if ($image instanceof File) {
      $style = $this->imageStyleStorage->load('thumbnail');
      $picture = $style?->buildUrl($image->getFileUri());
    }

    return $picture;
  }

}
