<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Utility;

use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;


class ChannelProvider {

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * Get Channel from Entity.
   *
   * @param $entity
   *  The entity item.
   * @return ChannelInterface|bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getChannel($entity): ChannelInterface|bool {
    $channel = $this->entityTypeManager->getStorage(ChannelInterface::ENTITY_TYPE_ID)->loadByProperties([
      'entity_id' => $entity->uuid(),
    ]);
    return reset($channel);
  }
}
