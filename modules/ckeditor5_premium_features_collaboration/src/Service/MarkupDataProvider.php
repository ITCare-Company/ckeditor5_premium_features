<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Service;

use DOMXPath;
use Drupal\ckeditor5_premium_features_collaboration\EditorElement\SuggestionItem;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use function in_array;

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
    $users = $this->getSuggestionsUserIds($content);

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
   * Gets the list of suggestions IDs.
   *
   * @param string $content
   *   The content containg HTML markup.
   *
   * @return string[]
   *   The list of suggestions IDs.
   */
  public function getSuggestionsIds(string $content): array {
    $suggestions = $this->getSuggestionsData($content);

    return array_map(fn ($suggestion) => $suggestion->getSuggestionId(), $suggestions);
  }

  /**
   * Gets the user ids stored in the suggestions.
   *
   * @param string $content
   *   The content containg HTML markup.
   *
   * @return int[]
   *   The user IDs.
   */
  protected function getSuggestionsUserIds(string $content): array {
    $suggestions = $this->getSuggestionsData($content);

    return array_map(fn ($suggestion) => $suggestion->getUserId(), $suggestions);
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
   * Gets the suggestion data from the given content.
   *
   * @param string $content
   *   The markup string.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\EditorElement\SuggestionItem[]
   *   The list of the suggestions.
   */
  protected function getSuggestionsData(string $content): array {
    $suggestions = $this->loadSuggestionsFromMarkup($content);
    $data = [];
    foreach ($suggestions as $suggestion) {
      if (!$suggestion->hasAttribute('name')) {
        continue;
      }

      $data[] = new SuggestionItem($suggestion->getAttribute('name'));
    }

    return $data;
  }

}
