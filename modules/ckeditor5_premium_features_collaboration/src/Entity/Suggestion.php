<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the CKEditor5 Premium features "Suggestion" entity.
 *
 * @ContentEntityType(
 *   id = "ckeditor5_suggestion",
 *   label = @Translation("CKEditor5 Suggestion"),
 *   base_table = "ckeditor5_suggestion",
 *   entity_keys = {
 *      "id" = "id",
 *      "uid" = "uid",
 *      "entity_type" = "entity_type",
 *      "entity_id" = "entity_id",
 *   },
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *   }
 * )
 */
class Suggestion extends ContentEntityBase implements SuggestionInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = [];

    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Suggestion ID'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    // We need to have two string (non-reference) fields,
    // because the entity id is not available before
    // the entity is created. We are only able to store some temp hash.
    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setRequired(TRUE)
      ->setDescription(t('The target entity type.'));

    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The Entity ID.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the suggestion was created.'));

    $fields['has_comments'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Has comments'))
      ->setRequired(TRUE)
      ->setDefaultValue(FALSE)
      ->setDescription(t('A boolean indicating whether the suggestion has comments.'));

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('The suggestion data.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return [
      'user' => $this->getAuthorId(),
      'created' => $this->getCreatedTime(),
      'has_comments' => $this->hasComments(),
    ];
  }

  /**
   * Gets the suggestion author ID.
   *
   * @return int|null
   *   The author ID.
   */
  public function getAuthorId(): ?int {
    $field = $this->get('uid');

    return $field->isEmpty() ? NULL : (int) $field->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return (int) $this->get('created')->value;
  }

  public function getTargetEntityType(): string {
    return (string) $this->get('entity_type')->value;
  }

  public function getData(bool $raw = FALSE): string|array {
    $data = (string) $this->get('data')->value;

    return $raw ? $data : (array) Json::decode($data);
  }

  public function setData(array|string $data): static {
    $data = is_array($data) ? Json::encode($data) : $data;
    $this->set('data', $data);

    return $this;
  }

  public function setCommentState(bool $state): static {
    $this->set('has_comments', $state);

    return $this;
  }

  public function hasComments(): bool {
    return (bool) $this->get('has_comments')->value;
  }

}
