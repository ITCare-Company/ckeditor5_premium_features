<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Access\AccessException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides the storage class for the Revision entity.
 */
class RevisionStorage extends SqlContentEntityStorage implements CollaborationEntityStorageInterface, EditorDataStorageProviderInterface {
  use CollaborationEntityStorageTrait;

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
  public function serializeCollection(array $entities): string {
    $suggestions = $entities;

    $serialized = [];
    foreach ($suggestions as $suggestion) {
      $serialized[] = $suggestion->toArray();
    }

    return (string) json_encode($serialized);
  }

  /**
   * {@inheritdoc}
   */
  public function add(array $raw_data): CollaborationEntityInterface {
    $raw_data = Revision::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $id = $data->getAlnum('id');
    if ($id == 'initial') {
      // Avoid duplicate IDs.
      $id = $data->getInt('entity_id') . '-initial';
    }

    $object_data = [
      'id' => $id,
      'uid' => $this->user->id(),
      'entity_id' => $data->getInt('entity_id'),
    ];

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionInterface $revision */
    $revision = $this->create($object_data);
    $revision
      ->setEntityTypeTargetId($data->get('entity_type', ''))
      ->setName($data->get('name'))
      ->setAuthors($data->get('authors'))
      ->setDiffData($data->get('diff_data'))
      ->setPreviousVersion($data->get('previous_version'))
      ->setCurrentVersion($data->get('current_version'));

    if (!$revision->access('update')) {
      throw new AccessException();
    }

    $revision->save();

    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface {
    if (!$entity->access('update')) {
      throw new AccessException();
    }

    $raw_data = Revision::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $entity
      ->setName($data->get('name'))
      ->save();

    return $entity;
  }


}
