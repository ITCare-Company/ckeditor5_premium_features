<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\ckeditor5_premium_features\CKeditorPremiumLoggerChannelTrait;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Access\AccessException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\filter\FilterFormatInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides the storage class for the Comments entity.
 */
class CommentsStorage extends SqlContentEntityStorage implements
    CollaborationEntityStorageInterface,
    EditorDataStorageProviderInterface,
    CollaborationSuggestionDependingStorageInterface,
    CollaborationContentFilteringStorageInterface,
    CollaborationEntityEventDispatcherInterface {

  use CollaborationEntityStorageTrait;
  use CKeditorPremiumLoggerChannelTrait;

  /**
   * Suggestion IDs list.
   *
   * @var array
   */
  protected array $suggestionIds;

  /**
   * Filter format.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected FilterFormatInterface $filterFormat;

  /**
   * Creates the storage instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   THe current user object.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   Event dispatcher service.
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
  public function serializeCollection(array $entities, $format = NULL): string {
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

    $stored_comments = $this->loadByEntity($entity, $item_key);

    $this->filterSourceData($source_data);

    foreach ($source_data as $thread_data) {
      $thread_id = $thread_data['threadId'];

      foreach ($thread_data['comments'] as $position => $element_data) {

        $element_data['position'] = $position;
        $element_data['thread_id'] = $thread_id;
        $element_data['id'] = $element_data['commentId'];
        $element_data['is_reply'] = $position > 0 || $this->hasSuggestionId($thread_id);

        $element_data = array_merge($element_data, $this->getCommonData($entity, $item_key));

        $entity_list[] = $element_data;

        // This way, in a result, we'll have a list of Comment entities that
        // we are storing, but were deleted by the user.
        unset($stored_comments[$element_data['commentId']]);
      }
    }

    if (!empty($stored_comments)) {
      try {
        $this->delete($stored_comments);
      }
      catch (EntityStorageException $e) {
        $this->logException("Comment storage error while deleting old entities.", $e);
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
  public function dispatchUpdatedEntity(CollaborationEntityInterface $oldEntity, CollaborationEntityInterface $newEntity): void {
    // Comment Storage does not supports comment updates events.
  }

  /**
   * {@inheritdoc}
   */
  public function dispatchNewEntity(CollaborationEntityInterface $entity): void {
    $this->event_dispatcher->dispatch(
      new CollaborationEventBase($entity, $this->user, CollaborationEventBase::COMMENT_ADDED),
      CollaborationEventBase::COMMENT_ADDED
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setSuggestionIds(array $suggestion_ids): void {
    $this->suggestionIds = $suggestion_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function hasSuggestionId(string $suggestion_id): bool {
    return in_array($suggestion_id, $this->suggestionIds);
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceFilterFormat(FilterFormatInterface $filter_format): void {
    $this->filterFormat = $filter_format;
  }

  /**
   * {@inheritdoc}
   */
  public function filterSourceData(array &$source_data): void {
    if (!isset($this->filterFormat)) {
      return;
    }

    $restrictions = $this->filterFormat->getHtmlRestrictions();

    $allowed_tags = !empty($restrictions['allowed']) ? array_keys($restrictions['allowed']) : Xss::getHtmlTagList();

    $allowed_tags = array_merge($allowed_tags, [
      'p',
      'li',
      'ol',
      'ul',
      'strong',
      'i',
      'span',
    ]);

    foreach ($source_data as &$thread_data) {
      foreach ($thread_data['comments'] as &$element_data) {
        $element_data['content'] = Xss::filter($element_data['content'], $allowed_tags);
      }
    }
  }

  /**
   * Returns a list of comments that belong to the same thread.
   *
   * @param string $entityType
   *   Type of source entity.
   * @param string $entityId
   *   Source entity ID.
   * @param string $threadId
   *   Thread ID.
   */
  public function getCommentsThread(string $entityType, string $entityId, string $threadId): array {
    $query = $this->getQuery()
      ->accessCheck(TRUE)
      ->condition('entity_type', $entityType)
      ->condition('entity_id', $entityId)
      ->condition('thread_id', $threadId)
      ->sort('created');

    $entity_ids = $query->execute();

    if (empty($entity_ids)) {
      return [];
    }

    return $this->loadMultiple($entity_ids);
  }

}
