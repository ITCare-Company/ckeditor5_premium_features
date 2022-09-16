<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the interface for the CKEditor5 "Message Item" entity.
 */
interface MessageItemInterface {

  public const ENTITY_TYPE_ID = 'ckeditor5_message_item';

  public function getType(): string;

  public function getMessageContent(): string;

  public function getEventType(): string;

  public function getRelatedEntityId(): string;

  public function getRelatedEntityType(): string;

  public function getRelatedEntity(): EntityInterface|null;

  public function getThread(): array;
}
