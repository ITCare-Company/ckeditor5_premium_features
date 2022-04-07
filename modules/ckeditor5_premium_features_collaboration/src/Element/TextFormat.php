<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\EditorDataStorageProviderInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\StorageDataNormalizationAwareInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Service\MarkupDataProviderInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat {

  public const STORAGE_KEY = 'ckeditor5-premium';

  /**
   * Process the text_format form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   * @param array $complete_form
   *   The form structure.
   *
   * @return array
   *   The element data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    static::addSubmitCallback($complete_form);

    $id = static::getElementId();
    $id_attribute = 'data-' . static::STORAGE_KEY . '-element-id';

    $content = $element['#default_value'] ?? '';

    // Setup the users.
    $users = static::getDataProvider()->getMarkupUsers($content);
    $element['#attached']['drupalSettings']['ckeditor5Premium']['users'] = $users;

    // Setup the suggestions.
    $suggestions_ids = static::getDataProvider()->getSuggestionsIds($content);
    $suggestions = static::getSuggestionStorage()->loadEditorDataFromIds($suggestions_ids);

    $element['value']['#attributes'][$id_attribute] = $id;
    $element['track_changes'] = [
      '#type' => 'hidden',
      '#title' => t('Track changes'),
      '#attributes' => [
        'class' => [
          'track-changes-data',
        ],
        $id_attribute => $id,
      ],
      '#default_value' => Json::encode($suggestions),
    ];

    // Setup the comments.
    $comments_ids = static::getDataProvider()->getCommentsIds($content);
    $comments = static::getCommentStorage()->loadEditorDataFromIds($comments_ids);

    $element['comments'] = [
      '#type' => 'hidden',
      '#title' => t('Comments'),
      '#attributes' => [
        'class' => [
          'comments-data',
        ],
        $id_attribute => $id,
      ],
      '#default_value' => json_encode($comments),
    ];

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $items[$id] = $element['#parents'];
    $form_state->set(static::STORAGE_KEY, $items);

    return $element;
  }

  /**
   * The complete form submit callback.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function onCompleteFormSubmit(array &$form, FormStateInterface $form_state): void {
    $entity = $form_state->getFormObject()->getEntity();

    if (!$entity instanceof EntityInterface) {
      return;
    }

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $features = [
      'track_changes' => static::getSuggestionStorage(),
      'comments' => static::getCommentStorage(),
    ];

    foreach ($items as $item_parents) {
      foreach ($features as $key => $storage) {
        $source = $form_state->getValue([...$item_parents, $key]);
        $source_data = (array) Json::decode($source);
        static::doStorageOperations($source_data, $storage, $entity);
      }
    }
  }

  /**
   * Adds the submit callback to the form.
   *
   * @param array $form
   *   The form structure.
   */
  private static function addSubmitCallback(array &$form): void {
    $submit_callback = [static::class, 'onCompleteFormSubmit'];
    $keys = [
      ['#submit'],
      ['actions', 'submit', '#submit'],
    ];
    foreach ($keys as $key) {
      if (NestedArray::keyExists($form, $key)) {
        $callbacks = NestedArray::getValue($form, $key) ?? [];
        $callbacks[] = $submit_callback;
        NestedArray::setValue($form, $key, $callbacks);
      }
    }
  }

  /**
   * Execute the storage commands based on the given markup data.
   *
   * @param array $markup_data
   *   The data stored in the markup.
   * @param object $storage
   *   The related type storage.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity related to the text format item.
   */
  private static function doStorageOperations(array $markup_data, object $storage, EntityInterface $entity): void {
    if ($storage instanceof StorageDataNormalizationAwareInterface) {
      $markup_data = $storage->normalize($markup_data);
    }
    foreach ($markup_data as $element_data) {
      $data_entity = $storage->load($element_data['id']);
      if ($data_entity instanceof EntityInterface) {
        $storage->update($data_entity, $element_data);
      }
      else {
        $element_data['entity_type'] = $entity->getEntityTypeId();
        $element_data['entity_id'] = $entity->id();
        $storage->add($element_data);
      }
    }
  }

  /**
   * Gets the element unique HTML ID.
   *
   * @return string
   *   The ID.
   */
  private static function getElementId(): string {
    $id = 'id-' . Crypt::randomBytesBase64(8);

    return Html::getId($id);
  }

  /**
   * Gets the markup data provider.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Service\MarkupDataProviderInterface
   *   The markup data provider instance.
   */
  private static function getDataProvider(): MarkupDataProviderInterface {
    return \Drupal::service('ckeditor5_premium_features_collaboration.makrup_data_provider');
  }

  /**
   * Gets the suggestion entity storage.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\EditorDataStorageProviderInterface
   *   The storage object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private static function getSuggestionStorage(): EditorDataStorageProviderInterface {
    return \Drupal::entityTypeManager()->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
  }

  /**
   * Gets the suggestion entity storage.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\EditorDataStorageProviderInterface
   *   The storage object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private static function getCommentStorage(): EditorDataStorageProviderInterface {
    return \Drupal::entityTypeManager()->getStorage(CommentInterface::ENTITY_TYPE_ID);
  }

}
