<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

use Drupal\ckeditor5_premium_features\CKeditorPremiumLoggerChannelTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Provides the storage class for the Channel entity.
 */
class ChannelStorage extends SqlContentEntityStorage {

  use CKeditorPremiumLoggerChannelTrait;

  /**
   * Creates a new channel entity.
   *
   * @param $entity
   *   The entity item.
   * @param string $channel_id
   *   Channel ID.
   *
   * @return \Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelInterface
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createChannel(EntityInterface $entity, string $channel_id): ChannelInterface {
    $properties = [
      'id' => $channel_id,
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->uuid(),
      'created' => time(),
    ];

    $channel = parent::create($properties);
    $channel->save();

    return $channel;
  }

  /**
   * Returns a channel entity referencing passed entity.
   *
   * @param $entity
   *   The entity item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadByEntity(EntityInterface $entity): ?ChannelInterface {
    $properties = [
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->uuid(),
    ];

    $channel = $this->entityTypeManager->getStorage(ChannelInterface::ENTITY_TYPE_ID)
      ->loadByProperties($properties);

    if (empty($channel)) {
      return NULL;
    }

    return reset($channel);
  }

}
