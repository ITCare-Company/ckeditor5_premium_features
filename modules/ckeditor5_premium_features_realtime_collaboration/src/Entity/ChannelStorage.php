<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

use Drupal\ckeditor5_premium_features\CKeditorPremiumLoggerChannelTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Provides the storage class for the Channel entity.
 */
class ChannelStorage extends SqlContentEntityStorage {

  use CKeditorPremiumLoggerChannelTrait;

  /**
   * Creates a new channel entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity item.
   * @param string $channel_id
   *   Channel ID.
   * @param string $element_id
   *   ID of the field element.
   *
   * @return \Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelInterface
   *   Channel entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createChannel(EntityInterface $entity, string $channel_id, string $element_id): ChannelInterface {
    $properties = [
      'id' => $channel_id,
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->uuid(),
      'created' => time(),
      'key_id' => $element_id,
    ];

    $channel = parent::create($properties);
    try {
      $channel->save();
    }
    catch (EntityStorageException) {
      $channel_stored = $this->load($channel_id);

      // Below is only a backward compatibility with sites using the Channel
      // entities, but before adding the `key_id` property.
      if ($channel_stored instanceof Channel && $channel_stored->hasField('key_id') &&
        empty($channel_stored->get('key_id')->getString())) {
        $channel_stored->set('key_id', $element_id);
        $channel_stored->save();

        return $channel_stored;
      }
    }

    return $channel;
  }

  /**
   * Returns a channel entity referencing passed entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity item.
   * @param string $element_id
   *   ID of the field element.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadByEntity(EntityInterface $entity, string $element_id): ?ChannelInterface {
    $properties = [
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->uuid(),
      'key_id' => $element_id,
    ];

    $channel = $this->entityTypeManager->getStorage(ChannelInterface::ENTITY_TYPE_ID)
      ->loadByProperties($properties);

    if (empty($channel)) {
      return NULL;
    }

    return reset($channel);
  }

}
