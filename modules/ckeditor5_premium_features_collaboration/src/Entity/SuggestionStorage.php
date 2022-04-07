<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides the storage class for the Suggestion entity.
 */
class SuggestionStorage extends SqlContentEntityStorage implements CollaborationEntityStorageInterface, EditorDataStorageProviderInterface {

  /**
   * Creates the storage instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   THe current user object.
   * @param mixed ...$parent_arguments
   *   The parent paramters.
   */
  public function __construct(
    protected AccountProxyInterface $user,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('current_user'),
      $entity_type,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadEditorDataFromIds(array $ids): array {
    $suggestions = $this->loadMultiple($ids);

    $normalized = [];
    foreach ($suggestions as $suggestion) {
      $normalized[] = $suggestion->toArray();
    }

    return $normalized;
  }

  /**
   * {@inheritdoc}
   */
  public function add(array $raw_data): CollaborationEntityInterface {
    $raw_data = Suggestion::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $object_data = [
      'id' => $data->getAlnum('id'),
      'entity_id' => $data->getInt('entity_id'),
    ];

    $original_suggestion = $this->loadOriginalSuggestionFromData($data);
    $has_original = $original_suggestion instanceof SuggestionInterface;

    $callback = $has_original ? 'getSuggestionEntityData' : 'getSuggestionData';
    $source = $has_original ? $original_suggestion : $data;

    [
      $object_data,
      $suggestion_data,
      $attributes,
      $type,
    ] = call_user_func([__CLASS__, $callback], $object_data, $source);

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion */
    $suggestion = $this->create($object_data);
    $suggestion->setEntityTypeTargetId($data->get('entity_type', ''));
    $suggestion->setType($type);
    $suggestion->setData($suggestion_data);
    $suggestion->setAttributes($attributes);
    $suggestion->save();

    return $suggestion;
  }

  /**
   * {@inheritdoc}
   */
  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface {
    $raw_data = Suggestion::normalize($raw_data);
    $data = new ParameterBag($raw_data);
    $has_comments = $data->getBoolean('has_comments');

    $entity
      ->setCommentState($has_comments)
      ->save();

    return $entity;
  }

  /**
   * Loads the original suggestion if present in the data.
   *
   * @param \Symfony\Component\HttpFoundation\ParameterBag $data
   *   The data key/value.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface|null
   *   The suggestion entity or null.
   */
  protected function loadOriginalSuggestionFromData(ParameterBag $data): ?SuggestionInterface {
    $original_suggestion_id = $data->getAlnum('original');

    return $original_suggestion_id ? $this->load($original_suggestion_id) : NULL;
  }

  /**
   * Gets the suggestion data based on what was passed in the data.
   *
   * @param array $current_data
   *   The common data already added.
   * @param \Symfony\Component\HttpFoundation\ParameterBag $data
   *   The data key/value.
   *
   * @return array
   *   The data required to be stored on the entity.
   */
  protected function getSuggestionData(array $current_data, ParameterBag $data): array {
    $object_data = [
      'uid' => $this->user->id(),
    ] + $current_data;

    return [
      $object_data,
      (string) $data->get('data'),
      (array) $data->get('attributes'),
      (string) $data->get('type'),
    ];
  }

  /**
   * Gets the suggestion data based on the suggestion entity..
   *
   * @param array $current_data
   *   The common data already added.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion
   *   The suggestion entity.
   *
   * @return array
   *   The data required to be stored on the entity.
   */
  protected function getSuggestionEntityData(array $current_data, SuggestionInterface $suggestion): array {
    $object_data = [
      'uid' => $suggestion->getAuthorId(),
      'type' => $suggestion->getType(),
      'created' => $suggestion->getCreatedTime(),
    ] + $current_data;

    return [
      $object_data,
      $suggestion->getData(),
      $suggestion->getAttributes(),
      $suggestion->getType(),
    ];
  }

}
