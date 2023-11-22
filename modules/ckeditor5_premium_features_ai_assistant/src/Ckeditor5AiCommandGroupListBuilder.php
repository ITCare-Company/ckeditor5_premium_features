<?php

namespace Drupal\ckeditor5_premium_features_ai_assistant;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of ckeditor 5 ai commands groups.
 */
class Ckeditor5AiCommandGroupListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('ID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\ckeditor5_premium_features_ai_assistant\Ckeditor5AiCommandGroupInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    return $row + parent::buildRow($entity);
  }

}
