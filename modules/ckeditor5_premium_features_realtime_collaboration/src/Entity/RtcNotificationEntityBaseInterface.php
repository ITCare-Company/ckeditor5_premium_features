<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\user\UserInterface;

/**
 * Base class of rtc notification class.
 */
interface RtcNotificationEntityBaseInterface {

  /**
   *
   */
  public function setId(string $id): static;

  /**
   *
   */
  public function getId(): string;

  /**
   *
   */
  public function getAuthor(): ?UserInterface;

  /**
   *
   */
  public function setAuthor(?UserInterface $author): static;

  /**
   *
   */
  public function getAuthorId(): int;

  /**
   *
   */
  public function getEntityTypeTargetId(): string;

  /**
   *
   */
  public function setEntityTypeTargetId(string $id): static;

  /**
   *
   */
  public function getReferencedEntity(): FieldableEntityInterface;

  /**
   *
   */
  public function setReferencedEntity(FieldableEntityInterface $entity): static;

  /**
   *
   */
  public function setThread(array $thread): static;

  /**
   *
   */
  public function getThread(): array;

  /**
   * @param string $threadId
   */
  public function setThreadId(string $threadId): static;

  /**
   * @return string
   */
  public function getThreadId(): string;

  /**
   * @return string
   */
  public function id(): string;

}
