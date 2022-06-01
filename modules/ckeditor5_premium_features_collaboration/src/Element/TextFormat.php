<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\StorageDataNormalizationAwareInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage;
use Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat {

  public const STORAGE_KEY = 'ckeditor5-premium';

  /**
   * The suggestion storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage
   */
  protected SuggestionStorage $suggestionStorage;

  /**
   * The comments storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage
   */
  protected CommentsStorage $commentsStorage;

  /**
   * The revision storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionStorage
   */
  protected RevisionStorage $revisionStorage;

  /**
   * Creates the text format element instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface $editorStorageHandler
   *   The editor storage handler.
   * @param \Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider $userDataProvider
   *   The user data storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EditorStorageHandlerInterface $editorStorageHandler,
    protected UserDataProvider $userDataProvider,
  ) {
    $this->suggestionStorage = $this->entityTypeManager->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
    $this->commentsStorage = $this->entityTypeManager->getStorage(CommentInterface::ENTITY_TYPE_ID);
    $this->revisionStorage = $this->entityTypeManager->getStorage(RevisionInterface::ENTITY_TYPE_ID);
  }

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
  public function processElement(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    if (!$this->editorStorageHandler->isCkeditor5($element)) {
      // Don't process if this is not a CKEditor5.
      return $element;
    }

    if (!$this->editorStorageHandler->hasCollaborationFeaturesEnabled($element)) {
      // Don't process as the editor does not have
      // any collaboration features enabled.
      return $element;
    }

    $entity = $form_state->getFormObject()->getEntity();
    if (!$entity instanceof EntityInterface) {
      // Do not process anything, the entity is missing.
      return $element;
    }

    // Attach annotation sidebar.
    AnnotationSidebar::process($element, $form_state, $complete_form);

    $this->addSubmitCallback($complete_form);

    $id = $this->getElementId();
    $id_attribute = 'data-' . static::STORAGE_KEY . '-element-id';

    $default_element_keys = [
      '#type' => 'textarea',
      '#attributes' => [
        // The admin theme may vary, so this is the safest solution.
        //'style' => 'display: none;',
        $id_attribute => $id,
      ],
      '#theme_wrappers' => [],
    ];

    // Setup the suggestions.
    $suggestions = $this->suggestionStorage->loadByEntity($entity);

    $element['value']['#attributes'][$id_attribute] = $id;
    $element['track_changes'] = [
      '#default_value' => $this->suggestionStorage->serializeCollection($suggestions),
    ] + $default_element_keys;
    $element['track_changes']['#attributes']['class'] = ['track-changes-data'];

    // Setup the comments.
    $comments = $this->commentsStorage->loadByEntity($entity);

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[] $users_data */
    $users_data = array_merge($comments, $suggestions);
    $element['#attached']['drupalSettings']['ckeditor5Premium']['users'] = $this->userDataProvider->getFromEntities($users_data);

    $element['comments'] = [
      '#default_value' => $this->commentsStorage->serializeCollection($comments),
    ] + $default_element_keys;
    $element['comments']['#attributes']['class'] = ['comments-data'];

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $items[$id] = $element['#parents'];
    $form_state->set(static::STORAGE_KEY, $items);


    // Setup the revision history.
    $revisions = $this->revisionStorage->loadByEntity($entity);

    $element['revision_history'] = [
      // @todo load serialized data from storage (simillar to comments and track changes storage).
      '#default_value' => $this->revisionStorage->serializeCollection($revisions),
    ] + $default_element_keys;
    $element['revision_history']['#attributes']['class'] = ['revision-history-data'];

    // Add the container for the revision list.
    $element['revision_history_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['revision-history-container-data'],
        $id_attribute => $id,
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['editor-container'],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['revision-viewer-editor'],
          ],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['revision-viewer-sidebar'],
          ],
        ],
      ],
    ];

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
  public function completeFormSubmit(array &$form, FormStateInterface $form_state): void {
    $entity = $form_state->getFormObject()->getEntity();

    if (!$entity instanceof EntityInterface) {
      // Do not process anything, the entity is missing.
      return;
    }

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $features = [
      'track_changes' => $this->suggestionStorage,
      'comments' => $this->commentsStorage,
      'revision_history' => $this->revisionStorage,
    ];

    foreach ($items as $item_parents) {
      foreach ($features as $key => $storage) {
        $source = $form_state->getValue([...$item_parents, $key]);
        $source_data = (array) json_decode($source, TRUE);
        $this->doStorageOperations($source_data, $storage, $entity);
      }
    }
  }

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
    /** @var \Drupal\ckeditor5_premium_features_collaboration\Element\TextFormat $service */
    $service = \Drupal::service('ckeditor5_premium_features_collaboration.element.text_format');
    return $service->processElement($element, $form_state, $complete_form);
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
    /** @var \Drupal\ckeditor5_premium_features_collaboration\Element\TextFormat $service */
    $service = \Drupal::service('ckeditor5_premium_features_collaboration.element.text_format');
    $service->completeFormSubmit($form, $form_state);
  }

  /**
   * Adds the submit callback to the form.
   *
   * @param array $form
   *   The form structure.
   */
  private function addSubmitCallback(array &$form): void {
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
  private function doStorageOperations(array $markup_data, object $storage, EntityInterface $entity): void {
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
  private function getElementId(): string {
    $id = 'id-' . Crypt::randomBytesBase64(8);

    return Html::getId($id);
  }

}
