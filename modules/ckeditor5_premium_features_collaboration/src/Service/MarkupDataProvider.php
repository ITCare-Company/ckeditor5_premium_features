<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Service;

use DOMXPath;
use DOMNodeList;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use function explode;
use function in_array;
use function is_numeric;

/**
 * The utility service for handling the data stored in the HTML markup.
 */
class MarkupDataProvider implements MarkupDataProviderInterface {

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

  public function __construct(
    protected  AccountProxyInterface $account,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->imageStyleStorage = $entity_type_manager->getStorage('image_style');
  }

  /**
   * Gets the users data stored in suggestions.
   *
   * @param string $content
   *   The content containg HTML markup.
   *
   * @return array
   *   The users data.
   */
  public function getSuggestionsUsers(string $content): array {
    $suggestions = $this->loadSuggestionsFromMarkup($content);
    $users = $this->getSuggestionsUserIds($suggestions);

    if (!in_array($this->account->id(), $users)) {
      $users[] = $this->account->id();
    }

    $users_data = [];
    foreach ($users as $id) {
      $user = $this->userStorage->load($id);

      if (!$user instanceof UserInterface) {
        continue;
      }

      // @todo To be consider if we need to restrict the view access or not.
      if (!$user->access('view')) {
        continue;
      }

      $users_data[$user->id()] = [
        'id' => $user->id(),
        'name' => $user->getDisplayName(),
        'avatar' => $this->getUserPicture($user),
      ];
    }

    return $users_data;
  }

  /**
   * Loads the suggestion elements from the given markup.
   *
   * @param string $content
   *   The markup string.
   *
   * @return \DOMNodeList|false|mixed
   *   The founded suggestions.
   */
  protected function loadSuggestionsFromMarkup(string $content): mixed {
    $dom = Html::load($content);
    $xpath = new DOMXPath($dom);

    return $xpath->query('//suggestion-start');
  }

  /**
   * Gets the user ids stored in the suggestions.
   *
   * @param \DOMNodeList $suggestions
   *   The suggestion elements.
   *
   * @return int[]
   *   The user IDs.
   */
  protected function getSuggestionsUserIds(DOMNodeList $suggestions): array {
    $users = [];

    foreach ($suggestions as $suggestion) {
      if (!$suggestion->hasAttribute('name')) {
        continue;
      }

      [$type, $suggestion_id, $user_id] = explode(':', $suggestion->getAttribute('name'));

      if (is_numeric($user_id) && !in_array($user_id, $users)) {
        $users[] = $user_id;
      }
    }

    return $users;
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
