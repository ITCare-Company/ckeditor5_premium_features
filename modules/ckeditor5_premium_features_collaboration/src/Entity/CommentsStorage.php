<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Access\AccessException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides the storage class for the Comments entity.
 */
class CommentsStorage extends SqlContentEntityStorage implements
    CollaborationEntityStorageInterface,
    EditorDataStorageProviderInterface,
    CollaborationSuggestionDependingStorageInterface {

  use CollaborationEntityStorageTrait;

  protected array $suggestion_ids;

  /**
   * Creates the storage instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   THe current user object.
   * @param mixed ...$parent_arguments
   *   The parent parameters.
   */
  public function __construct(
    protected AccountProxyInterface $user,
    protected ContainerAwareEventDispatcher $event_dispatcher,
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
      $container->get('event_dispatcher'),
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
  public function serializeCollection(array $entities): string {
    $comments = $entities;
    $data = [];

    foreach ($comments as $comment) {
      $thread_id = $comment->getThreadId();
      $data[$thread_id][] = $comment->toArray();
    }

    $serialized = [];
    foreach ($data as $thread_id => $thread_comments) {
      $serialized[] = [
        'threadId' => $thread_id,
        'comments' => $thread_comments,
      ];
    }

    return (string) json_encode($serialized);
  }

  /**
   * {@inheritdoc}
   */
  public function normalize(array $data): array {
    $normalized = [];
    foreach ($data as $thread) {
      $thread_id = $thread['threadId'];
      $comments = $thread['comments'];

      foreach ($comments as $comment) {
        $comment['id'] = $comment['commentId'];
        $comment['threadId'] = $thread_id;

        $normalized[] = $comment;
      }
    }

    return $normalized;
  }

  /**
   * {@inheritdoc}
   */
  public function processSourceData(array $source_data, ContentEntityInterface $entity, string $item_key): array {
    $entity_list = [];

    foreach ($source_data as $thread_data) {
      $thread_id = $thread_data['threadId'];

      foreach ($thread_data['comments'] as $position => $element_data) {

        $element_data['position'] = $position;
        $element_data['thread_id'] = $thread_id;
        $element_data['id'] = $element_data['commentId'];
        $element_data['is_reply'] = $position > 0 || $this->hasSuggestionId($thread_id);

        $element_data = array_merge($element_data, $this->getCommonData($entity, $item_key));

        $entity_list[] = $element_data;
      }
    }

    return $entity_list;
  }

  /**
   * {@inheritdoc}
   */
  public function add(array $raw_data): CollaborationEntityInterface|NULL {
    $raw_data = Comment::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $object_data = [
      'id' => $data->getAlnum('id'),
      'uid' => $this->user->id(),
      'entity_id' => $data->getInt('entity_id'),
    ];
    $attributes = [
      'key' => $data->get('key'),
      'position' => $data->get('position'),
      'is_reply' => $data->get('is_reply'),
    ] + $data->get('attributes') ?? [];

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Comment $comment */
    $comment = $this->create($object_data);
    $comment->setEntityTypeTargetId($data->get('entity_type', ''))
      ->setThreadId($data->get('thread_id'))
      ->setContent($data->get('content'))
      ->setIsReply($raw_data['is_reply']);

    if (!$comment->access('update')) {
      throw new AccessException();
    }

    $comment->setAttributes($attributes);

    $comment->save();

    $this->event_dispatcher->dispatch(
      new CollaborationEventBase($comment, $this->user, CollaborationEventBase::COMMENT_ADDED),
      CollaborationEventBase::COMMENT_ADDED
    );

    return $comment;
  }

  /**
   * {@inheritdoc}
   */
  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface|NULL {
    if (!$entity->access('update')) {
      throw new AccessException();
    }

    $raw_data = Comment::normalize($raw_data);
    $data = new ParameterBag($raw_data);
    $attributes = $data->get('attributes') ?? [];

    $entity
      ->setThreadId($data->get('thread_id'))
      ->setContent($data->get('content'))
      ->setAttributes($attributes)
      ->save();

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setSuggestionIds(array $suggestion_ids): void {
    $this->suggestion_ids = $suggestion_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function hasSuggestionId(string $suggestion_id): bool {
    return in_array($suggestion_id, $this->suggestion_ids);
  }

}
