<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

/**
 * Provides the interface for the CKEditor5 "Comment" entity.
 */
interface CommentInterface {

  public const ENTITY_TYPE_ID = 'ckeditor5_comment';

  /**
   * Gets the thread ID.
   *
   * @return string
   *   The ID of the comment thread.
   */
  public function getThreadId(): string;

  /**
   * Sets the thread ID.
   *
   * @param string $id
   *   The ID to set.
   *
   * @return static
   *   The current object.
   */
  public function setThreadId(string $id): static;

  /**
   * Gets the comment content.
   *
   * @return string|null
   *   The content of the comment, defaults to null.
   */
  public function getContent(): ?string;

  /**
   * Sets the comment content.
   *
   * @param string $content
   *   The content to set.
   *
   * @return static
   *   The current object.
   */
  public function setContent(string $content): static;

}
