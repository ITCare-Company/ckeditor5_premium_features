<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

/**
 * Helper comment entity for dealing with notifications in rtc module.
 */
class RtcCommentNotificationEntity extends RtcNotificationEntityBase {

  public const ENTITY_TYPE_ID = 'ckeditor5_comment';

  /**
   * @var string
   */
  private string $content;

  /**
   * @var bool
   */
  private bool $isReply;

  /**
   * @var bool
   */
  private bool $isSuggestionComment = FALSE;

  /**
   * @var RtcSuggestionNotificationEntity|null
   */
  private ?RtcSuggestionNotificationEntity $relatedSuggestion = NULL;

  /**
   * @var string
   */
  private string $createdDate;

  /**
   *
   */
  public function getEntityTypeId(): string {
    return self::ENTITY_TYPE_ID;
  }

  /**
   * @param mixed $content
   */
  public function setContent($content): static {
    $this->content = $content;
    return $this;
  }

  /**
   *
   */
  public function getContent(): string {
    return $this->content;
  }

  /**
   * @param string $createdDate
   */
  public function setCreatedDate(string $createdDate): static {
    $this->createdDate = $createdDate;
    return $this;
  }

  /**
   *
   */
  public function getCreatedDate():string {
    return $this->createdDate;
  }

  /**
   *
   */
  public function isReply():bool {
    return $this->isReply;
  }

  /**
   *
   */
  public function setIsReply($isReply):static {
    $this->isReply = $isReply;
    return $this;
  }

  /**
   *
   */
  public function getContentPlain(): string|null {
    $content = $this->getContent();
    if (empty($content)) {
      return NULL;
    }

    return str_replace(chr(0xC2) . chr(0xA0), ' ', html_entity_decode(strip_tags($content)));
  }

  /**
   *
   */
  public function setIsSuggestionComment(bool $isSuggestionComment): static {
    $this->isSuggestionComment = $isSuggestionComment;
    return $this;
  }

  /**
   *
   */
  public function isSuggestionComment(): bool {
    return $this->isSuggestionComment;
  }

  /**
   *
   */
  public function setRelatedSuggestion(RtcSuggestionNotificationEntity $suggestion):static {
    $this->relatedSuggestion = $suggestion;
    return $this;
  }

  /**
   *
   */
  public function getRelatedSuggestion():RtcSuggestionNotificationEntity {
    return $this->relatedSuggestion;
  }

  /**
   *
   */
  public function getRelatedSuggestionAuthorId(): ?int {
    return $this->relatedSuggestion?->getAuthorId();
  }

}
