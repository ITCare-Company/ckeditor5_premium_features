<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the CKEditor5 Premium features "Comment" entity.
 *
 * @ContentEntityType(
 *   id = "ckeditor5_comment",
 *   label = @Translation("CKEditor5 Suggestion"),
 *   base_table = "ckeditor5_comment",
 *   entity_keys = {
 *      "id" = "id",
 *      "uid" = "uid",
 *      "entity_type" = "entity_type",
 *      "entity_id" = "entity_id",
 *   },
 *   handlers = {
 *     "storage" = "Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage",
 *   }
 * )
 */
class Comment extends CollaborationEntityBase implements CommentInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['thread_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Thread ID'))
      ->setRequired(TRUE)
      ->setDescription(t('The comment thread ID'));

    $fields['content'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Content'))
      ->setDescription(t('The content of the comment.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function getNormalizationMapping(bool $reversed): array {
    return [
      'threadId' => 'thread_id',
      'content' => 'content',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $data = parent::toArray();
    $data = [
      'content' => $this->getContent(),
    ] + $data;

    $normalized = static::normalize($data, TRUE);
    $normalized['commentId'] = $normalized['id'];
    unset($normalized['id']);

    return $normalized;
  }

  /**
   * Gets the thread ID.
   *
   * @return string
   *   The ID of the comment thread.
   */
  public function getThreadId(): string {
    return (string) $this->get('thread_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setThreadId(string $id): static {
    return $this->setMachineName('thread_id', $id);
  }

  /**
   * Gets the comment content.
   *
   * @return string|null
   *   The content of the comment, defaults to null.
   */
  public function getContent(): ?string {
    $field = $this->get('content');

    return $field->isEmpty() ? NULL : (string) $field->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setContent(string $content): static {
    // @todo Add some sanitization if needed.
    $this->set('content', $content);

    return $this;
  }

}
