<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

/**
 * Channel entity interface.
 */
interface ChannelInterface {
  public const ENTITY_TYPE_ID = 'ckeditor5_channel';

  /**
   * Getter for channel key ID.
   */
  public function getKeyId(): ?string;

  /**
   * Setter for key ID property.
   *
   * @param string $value
   *   Key ID value.
   */
  public function setKeyId(string $value): static;

}
