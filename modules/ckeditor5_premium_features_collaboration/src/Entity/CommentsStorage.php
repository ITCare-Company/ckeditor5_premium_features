<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides the storage class for the Comments entity.
 */
class CommentsStorage extends SqlContentEntityStorage implements CollaborationEntityStorageInterface, StorageDataNormalizationAwareInterface, EditorDataStorageProviderInterface {

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
    $comments = $this->loadMultipleByThreadsIds($ids);
    $data = [];

    foreach ($comments as $comment) {
      $thread_id = $comment->getThreadId();
      $data[$thread_id][] = $comment->toArray();
    }

    $normalized = [];
    foreach ($data as $thread_id => $thread_comments) {
      $normalized[] = [
        'threadId' => $thread_id,
        'comments' => $thread_comments,
      ];
    }

    return $normalized;
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
   * Gets the user related to the comments and specific thread.
   *
   * @param array $ids
   *   The comments IDs.
   * @param string $thread_id
   *   The thread id.
   *
   * @return int[]|string[]
   *   The id of the users.
   */
  public function getUserIdsByCommentsIdsAndThread(array $ids, string $thread_id): array {
    $query = $this->buildQuery($ids)
      ->condition('thread_id', $thread_id)
      ->execute();

    return array_keys($query->fetchAllKeyed('uid'));
  }

  /**
   * Loads the comments by the given thread.
   *
   * @param string $thread_id
   *   The ID of the thread.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface[]
   *   The comments.
   */
  public function loadByThread(string $thread_id): array {
    return $this->loadByProperties(['thread_id' => $thread_id]);
  }

  /**
   * Loads multiple threads comments by the given threads id.
   *
   * @param array $thread_ids
   *   The IDs of the threads,
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface[]
   *   The comments.
   */
  public function loadMultipleByThreadsIds(array $thread_ids): array {
    $results = [];

    foreach ($thread_ids as $id) {
      $results = array_merge($results, $this->loadByThread($id));
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function add(array $raw_data): CollaborationEntityInterface {
    $raw_data = Comment::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $object_data = [
      'id' => $data->getAlnum('id'),
      'uid' => $this->user->id(),
      'entity_id' => $data->getInt('entity_id'),
    ];

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface $comment */
    $comment = $this->create($object_data);
    $comment->setEntityTypeTargetId($data->get('entity_type', ''))
      ->setThreadId($data->get('thread_id'))
      ->setContent($data->get('content'))
      ->save();

    return $comment;
  }

  /**
   * {@inheritdoc}
   */
  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface {
    $raw_data = Comment::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $entity
      ->setThreadId($data->get('thread_id'))
      ->setContent($data->get('content'))
      ->save();

    return $entity;
  }

}
