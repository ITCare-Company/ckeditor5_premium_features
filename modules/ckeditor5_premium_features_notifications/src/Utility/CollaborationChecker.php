<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

class CollaborationChecker {

  const FIELD_TYPES_SUPPORTING_CKEDITOR = [
    'text_with_summary',
    'text',
    'text_long'
  ];

  /**
   * Checks if specified entity has potentially collaboration feature enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to be checked.
   */
  public static function hasCollaborationFeatures(EntityInterface $entity): bool {
    if (!$entity instanceof FieldableEntityInterface) {
      return FALSE;
    }

    $foundAnySupportedField = FALSE;
    $fieldDefinitions = $entity->getFieldDefinitions();

    foreach ($fieldDefinitions as $fieldDefinition) {
      if (!in_array($fieldDefinition->getType(), self::FIELD_TYPES_SUPPORTING_CKEDITOR)) {
        continue;
      }
      $foundAnySupportedField = TRUE;
    }

    return $foundAnySupportedField;
  }

}
