<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
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
 *     "storage" = "Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage",
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

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Suggestion type'))
      ->setRequired(TRUE)
      ->setSetting('machine_name', TRUE)
      ->setDescription(t('The editor suggestion type.'));

    // We need to have two string (non-reference) fields,
    // because the entity id is not available before
    // the entity is created. We are only able to store some temp hash.
    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setRequired(TRUE)
      ->setSetting('machine_name', TRUE)
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
      ->setSetting('json', TRUE)
      ->setDescription(t('The suggestion data.'));

    $fields['attributes'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Attributes'))
      ->setSetting('json', TRUE)
      ->setDescription(t('The suggestion attributes.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $data = [
      'id' => $this->id(),
      'uid' => $this->getAuthorId(),
      'created' => $this->getCreatedTime() * 1000,
      'type' => $this->getType(),
      'has_comments' => $this->hasComments(),
      'data' => $this->getData() ?: NULL,
      'attributes' => $this->getAttributes(),
    ];

    return static::normalizeData($data, TRUE);
  }

  public static function normalizeData(array $data, bool $reversed = FALSE) {
    $mapping = [
      'id' => 'id',
      'type' => 'type',
      'createdAt' => 'created',
      'hasComments' => 'has_comments',
      'data' => 'data',
      'attributes' => 'attributes',
      'entity_id' => 'entity_id',
      'entity_type' => 'entity_type',
    ];

    if ($reversed) {
      $mapping['authorId'] = 'uid';
      $mapping = array_flip($mapping);
    }

    $normalized = [];
    foreach ($data as $property => $value) {
      if (isset($mapping[$property])) {
        $normalized[$mapping[$property]] = $value;
      }
    }

    return $normalized;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorId(): ?int {
    $field = $this->get('uid');

    return $field->isEmpty() ? NULL : (int) $field->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return (string) $this->get('type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setType(string $type): static {
    return $this->setMachineName('type', $type);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return (int) $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeTargetId(): string {
    return (string) $this->get('entity_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityTypeTargetId(string $id): static {
    return $this->setMachineName('entity_type', $id);
  }

  /**
   * {@inheritdoc}
   */
  public function getData(bool $raw = FALSE): string|array {
    return $this->getJsonFieldValue('data', $raw);
  }

  /**
   * {@inheritdoc}
   */
  public function setData(array|string $data): static {
    return $this->setJsonFieldValue('data', $data);
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributes(bool $raw = FALSE): string|array {
    return $this->getJsonFieldValue('attributes', $raw);
  }

  /**
   * {@inheritdoc}
   */
  public function setAttributes(array|string $data): static {
    return $this->setJsonFieldValue('attributes', $data);
  }

  /**
   * {@inheritdoc}
   */
  public function setCommentState(bool $state): static {
    $this->set('has_comments', $state);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasComments(): bool {
    return (bool) $this->get('has_comments')->value;
  }

  /**
   * Gets the value of the fields containing the JSON data.
   *
   * @param string $field_name
   *   The name of the field.
   * @param bool $raw
   *   FALSE to return decoded, TRUE for having
   *   the raw string value.
   *
   * @return string|array
   *   The data decoded or raw.
   */
  protected function getJsonFieldValue(string $field_name, bool $raw = FALSE): array|string {
    $data = '';
    if ($this->hasField($field_name)) {
      $data = (string) $this->get($field_name)->value;
    }

    return $raw ? $data : (array) Json::decode($data);
  }

  /**
   * Sets the value of the fields containg the JSON data.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array|string $data
   *   The data value (decoded or raw)
   */
  protected function setJsonFieldValue(string $field_name, array|string $data): static {
    if ($this->hasField($field_name)) {
      $data = is_array($data) ? Json::encode($data) : $data;
      $this->set($field_name, $data);
    }

    return $this;
  }

  /**
   * Sets the string as the machine name.
   *
   * Adds some sanitizion methods before saving the value.
   *
   * @param string $field_name
   *   The name of the field.
   * @param string $value
   *   The value to be sanitized and stored.
   *
   * @return $this
   */
  protected function setMachineName(string $field_name, string $value): static {
    $name = Html::decodeEntities(strip_tags($value));
    $this->set($field_name, $name);

    return $this;
  }

}
