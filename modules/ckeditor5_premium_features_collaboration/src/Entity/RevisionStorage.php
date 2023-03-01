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
class RevisionStorage extends SqlContentEntityStorage implements
    CollaborationEntityStorageInterface,
    EditorDataStorageProviderInterface,
    StorageIdSpecificationAwareInterface {

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
    $serialized = [];
    usort($entities, function (RevisionInterface $a, RevisionInterface $b) {
      // First, sort by timestamp.
      $compare_result = $a->getCreatedTime() <=> $b->getCreatedTime();
      if (!$compare_result) {
        // In case of equal timestamps, sort by version precedence.
        $compare_result = $a->getCurrentVersion() <=> $b->getCurrentVersion();
        if (!$compare_result) {
          $compare_result = $a->getPreviousVersion() <=> $b->getPreviousVersion();
        }
      }
      return $compare_result;
    });

    foreach ($entities as $entity) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $serialized[] = $entity->toArray();
    }

    return (string) json_encode($serialized);
  }

  /**
   * {@inheritdoc}
   */
  public function add(array $raw_data): CollaborationEntityInterface|NULL {
    $raw_data = Revision::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $object_data = [
      'id' => $data->get('id'),
      'uid' => $data->getInt('creator'),
      'entity_id' => $data->getInt('entity_id'),
      'created' => $data->getInt('created'),
      'langcode' => $data->get('langcode'),
    ];
    $attributes = [
      'key' => $raw_data['key'],
    ] + $data->get('attributes') ?? [];

    $authors = $data->get('authors');
    if (!empty($authors) && $data->getInt('creator') > 0 && !in_array($data->getInt('creator'), $authors)) {
      $object_data['uid'] = reset($authors);
    }

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Revision $revision */
    $revision = $this->create($object_data);
    $revision
      ->setEntityTypeTargetId($data->get('entity_type', ''))
      ->setName($data->get('name'))
      ->setAuthors($data->get('authors'))
      ->setDiffData($data->get('diff_data'))
      ->setPreviousVersion($data->get('previous_version'))
      ->setCurrentVersion($data->get('current_version'));

    // Set the 'draft' attribute if the creator is empty.
    if (!$data->get('creator')) {
      $attributes['draft'] = TRUE;
    }
    $revision->setAttributes($attributes);

    if (!$revision->access('create') && !$revision->access('update')) {
      throw new AccessException();
    }

    $revision->save();
    return $revision;
  }

  /**
   * {@inheritdoc}
   */
  public function update(CollaborationEntityInterface $entity, array $raw_data): CollaborationEntityInterface|NULL {
    if (!$entity->access('create') && !$entity->access('update')) {
      throw new AccessException();
    }
    if (!$entity instanceof Revision) {
      return NULL;
    }

    $raw_data = Revision::normalize($raw_data);
    $data = new ParameterBag($raw_data);

    $attributes = [
      'key' => $raw_data['key'],
    ] + $data->get('attributes') ?? [];

    $entity
      ->setName($data->get('name'))
      ->setAuthors($data->get('authors'))
      ->setDiffData($data->get('diff_data'))
      ->setAttributes($attributes)
      ->setPreviousVersion($data->get('previous_version'))
      ->setCurrentVersion($data->get('current_version'))
      ->save();

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function isCommonId(string $id): bool {
    return $id == 'initial';
  }

}
