<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

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
class Suggestion extends CollaborationEntityBase implements SuggestionInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Suggestion type'))
      ->setRequired(TRUE)
      ->setSetting('machine_name', TRUE)
      ->setDescription(t('The editor suggestion type.'));

    $fields['has_comments'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Has comments'))
      ->setRequired(TRUE)
      ->setDefaultValue(FALSE)
      ->setDescription(t('A boolean indicating whether the suggestion has comments.'));

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setSetting('json', TRUE)
      ->setDescription(t('The suggestion data.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function getNormalizationMapping(bool $reversed): array {
    return [
      'type' => 'type',
      'hasComments' => 'has_comments',
      'data' => 'data',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $data = parent::toArray();
    $data = [
      'type' => $this->getType(),
      'has_comments' => $this->hasComments(),
      'data' => $this->getData() ?: NULL,
    ] + $data;

    return static::normalize($data, TRUE);
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

}
