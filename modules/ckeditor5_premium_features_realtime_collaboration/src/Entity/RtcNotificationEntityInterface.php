<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\user\UserInterface;

/**
 *
 */
interface RtcNotificationEntityInterface {

  /**
   * @param string $id
   * @return RtcNotificationEntityInterface
   */
  public function setId(string $id): static;

  /**
   * @return string
   */
  public function getId(): string;

  /**
   * @return \Drupal\user\UserInterface|null
   */
  public function getAuthor(): ?UserInterface;

  /**
   * @param \Drupal\user\UserInterface|null $author
   * @return RtcNotificationEntityInterface
   */
  public function setAuthor(?UserInterface $author): static;

  /**
   * @return int
   */
  public function getAuthorId(): int;

  /**
   * @return string
   */
  public function getEntityTypeTargetId(): string;

  /**
   * @param string $id
   * @return RtcNotificationEntityInterface
   */
  public function setEntityTypeTargetId(string $id): static;

  /**
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   */
  public function getReferencedEntity(): FieldableEntityInterface;

  /**
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   * @return RtcNotificationEntityInterface
   */
  public function setReferencedEntity(FieldableEntityInterface $entity): static;

  /**
   * @param array $thread
   * @return RtcNotificationEntityInterface
   */
  public function setThread(array $thread): static;

  /**
   * @return array
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

  /**
   * @return string
   */
  public function getEntityTypeId(): string;

}
