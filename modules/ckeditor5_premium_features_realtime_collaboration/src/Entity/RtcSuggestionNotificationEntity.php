<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Entity;

/**
 * Helper suggestion entity for dealing with notifications in rtc module.
 */
class RtcSuggestionNotificationEntity extends RtcNotificationEntityBase {

  public const ENTITY_TYPE_ID = 'ckeditor5_suggestion';

  /**
   * @var string|null
   */
  private ?string $chainId = NULL;

  /**
   * @var array|null
   */
  private ?array $chain = NULL;

  /**
   * @var bool
   */
  private bool $isHeadOfChain = FALSE;

  /**
   * @var bool
   */
  private bool $isInChain = FALSE;

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeId(): string {
    return self::ENTITY_TYPE_ID;
  }

  /**
   * @param array $chain
   * @return RtcSuggestionNotificationEntity
   */
  public function setChain(array $chain): static {
    $this->chain = $chain;
    return $this;
  }

  /**
   * @return array
   */
  public function getChain(): array {
    return $this->chain;
  }

  /**
   * @param string $chain_id
   * @return RtcSuggestionNotificationEntity
   */
  public function setChainId(string $chain_id): static {
    $this->chainId = $chain_id;
    return $this;
  }

  /**
   * @return string
   */
  public function getChainId(): string {
    return $this->chainId;
  }

  /**
   * @param bool $isInChain
   * @return RtcSuggestionNotificationEntity
   */
  public function setIsInChain(bool $isInChain): static {
    $this->isInChain = $isInChain;
    return $this;
  }

  /**
   * @return bool
   */
  public function isInChain(): bool {
    return $this->isInChain;
  }

  /**
   * @return bool
   */
  public function isHeadOfChain(): bool {
    return $this->isHeadOfChain;
  }

  /**
   * @param bool $isHeadOfChain
   * @return RtcSuggestionNotificationEntity
   */
  public function setIsHeadOfChain(bool $isHeadOfChain): static {
    $this->isHeadOfChain = $isHeadOfChain;
    return $this;
  }

}
