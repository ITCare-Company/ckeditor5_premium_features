<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the storage class for the Message entity.
 */
class MessageStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
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
   * Returns message entity matching passed user and document parameters.
   *
   * @param int $userId
   *   ID of the related user.
   * @param string $documentId
   *   ID of the related entity.
   * @param string $documentType
   *   Type of the related entity.
   */
  public function getMessageForUserAndDocument(int $userId, string $documentId, string $documentType): Message|NULL {
    $result = $this->loadByProperties([
      'uid' => $userId,
      'entity_id' => $documentId,
      'entity_type' => $documentType,
    ]);

    if (empty($result)) {
      return NULL;
    }

    return reset($result);
  }

  public function createMessage($userID, $entityId, $entityType) {
    return parent::create([
      'uid' => $userID,
      'entity_type' => $entityType,
      'entity_id' => $entityId
    ]);
  }

  /**
   * Get oldest unsent messages.
   *
   * @param int $range
   *   Max quantity of getting messages.
   * @return array
   *   Array of message entities.
   */
  public function getOldestMessages(int $range):array {
    $nids = $this->getQuery()
      ->accessCheck(FALSE)
      ->condition('sent', 0)
      ->sort('created', 'ASC')
      ->range(0, $range)
      ->execute();

    if ($nids) {
      return $this->loadMultiple($nids);
    }

    return [];
  }

}
