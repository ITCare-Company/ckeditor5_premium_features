<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the storage class for the Comments entity.
 */
class MessageStorage extends SqlContentEntityStorage {

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

  public function getMessageForUserAndDocument(int $userId, int $documentId) {
    $result = $this->loadByProperties([
      'uid' => $userId,
      'entity_id' => $documentId,
    ]);

    if (empty($result)) {
      return NULL;
    }

    return reset($result);
  }

  /**
   * {@inheritdoc}
   */
  public function add(array $raw_data): CollaborationEntityInterface|NULL {
//    $raw_data = Message::normalize($raw_data);
//    $data = new ParameterBag($raw_data);
//
//    $object_data = [
//      'id' => $data->getAlnum('id'),
//      'uid' => $this->user->id(),
//      'entity_id' => $data->getInt('entity_id'),
//    ];
//    $attributes = [
//      'key' => $raw_data['item_key'],
//      'position' => $raw_data['position'],
//      'is_reply' => $raw_data['is_reply'],
//    ];
//
//    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Message $comment */
//    $comment = $this->create($object_data);
//    $comment->setEntityTypeTargetId($data->get('entity_type', ''))
//      ->setThreadId($data->get('thread_id'))
//      ->setContent($data->get('content'))
//      ->setIsReply($raw_data['is_reply']);
//
//    if (!$comment->access('update')) {
//      throw new AccessException();
//    }
//
//    $comment->setAttributes($attributes);
//
//    $comment->save();
//
//    $this->event_dispatcher->dispatch(
//      new CollaborationEventBase($comment, $this->user, CollaborationEventBase::COMMENT_ADDED),
//      CollaborationEventBase::COMMENT_ADDED
//    );
//
//    return $comment;
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface|NULL {
//    if (!$entity->access('update')) {
//      throw new AccessException();
//    }
//
//    $raw_data = Message::normalize($raw_data);
//    $data = new ParameterBag($raw_data);
//
//    $entity
//      ->setThreadId($data->get('thread_id'))
//      ->setContent($data->get('content'))
//      ->save();
//
//    return $entity;
//  }

}
