<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the interface for the CKEditor5 "Message Item" entity.
 */
interface MessageItemInterface {

  public const ENTITY_TYPE_ID = 'ckeditor5_message_item';

  /**
   * Getter for item type.
   */
  public function getType(): string;

  /**
   * Getter for message content.
   */
  public function getMessageContent(): string;

  /**
   * Getter for event type.
   */
  public function getEventType(): string;

  /**
   * Getter for related entity ID.
   */
  public function getRelatedEntityId(): string;

  /**
   * Getter for related entity type.
   */
  public function getRelatedEntityType(): string;

  /**
   * Returns related document entity.
   */
  public function getRelatedEntity(): EntityInterface|null;

  /**
   * Returns a related collaboration entity thread.
   */
  public function getThread(): array;
}
