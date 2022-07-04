<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Component\Utility\Xss;
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
 *     "access" = "Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityAccessControlHandler",
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getContent(): ?string {
    $field = $this->get('content');

    return $field->isEmpty() ? NULL : self::xssFilter((string) $field->value);
  }

  /**
   * {@inheritdoc}
   */
  public function setContent(string $content): static {
    $this->set('content', self::xssFilter($content));

    return $this;
  }

  /**
   * Filter the entity content to avoid XSS vulnerabilities.
   *
   * @param string $content
   *   The text to filter.
   *
   * @return string
   *   Filtered text.
   */
  protected static function xssFilter(string $content): string {
    $tags = array_merge(Xss::getHtmlTagList(), ['p']);
    return Xss::filter($content, $tags);
  }

}
