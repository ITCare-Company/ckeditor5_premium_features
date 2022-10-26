<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationContentFilteringStorageInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationSuggestionDependingStorageInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\RevisionStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage;
use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface;
use Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\FilterFormatInterface;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
   * @param \Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings $collaborationSettings
   *   Collaboration settings helper.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EditorStorageHandlerInterface $editorStorageHandler,
    protected UserDataProvider $userDataProvider,
    protected CollaborationSettings $collaborationSettings,
    protected EventDispatcherInterface $eventDispatcher,
    protected AccountInterface $currentUser,
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
    if (!$this->editorStorageHandler->hasCollaborationFeaturesEnabled($element)) {
      // Don't process as the editor does not have
      // any collaboration features enabled.
      return $element;
    }

    $form_object = $form_state->getFormObject();
    $entity = NULL;

    if ($this->isFormTypeSupported($form_object)) {
      $entity = $form_object->getEntity();
    } else {
      // We still need to process in order to stop our integration from
      // throwing exceptions in console, but we'll block editor toolbar buttons.
      $element['#attached']['drupalSettings']['ckeditor5Premium']['disableCollaboration'] = TRUE;
    }

    // Attach annotation sidebar.
    AnnotationSidebar::process($element, $form_state, $complete_form);

    $this->addSubmitCallback($complete_form);

    $id = CKeditorFieldKeyHelper::getElementUniqueId($element['#id']);
    $id_attribute = 'data-' . static::STORAGE_KEY . '-element-id';

    $default_element_keys = [
      '#type' => 'textarea',
      '#attributes' => [
        // The admin theme may vary, so this is the safest solution.
        'style' => 'display: none;',
        $id_attribute => $id,
      ],
      '#theme_wrappers' => [],
    ];

    // Setup the suggestions.
    $suggestions = $entity ? $this->suggestionStorage->loadByEntity($entity, $id) : [];

    $element['value']['#attributes'][$id_attribute] = $id;
    $element['track_changes'] = [
      '#default_value' => $this->suggestionStorage->serializeCollection($suggestions),
    ] + $default_element_keys;
    $element['track_changes']['#attributes']['class'] = ['track-changes-data'];

    // Setup the comments.
    $comments = $entity ? $this->commentsStorage->loadByEntity($entity, $id)  : [];

    $element['comments'] = [
      '#default_value' => $this->commentsStorage->serializeCollection($comments),
    ] + $default_element_keys;
    $element['comments']['#attributes']['class'] = ['comments-data'];

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $items[$id] = $element['#parents'];
    $form_state->set(static::STORAGE_KEY, $items);

    // Setup the revision history.
    $revisions = $entity ? $this->revisionStorage->loadByEntity($entity, $id)  : [];

    $element['revision_history'] = [
      '#default_value' => $this->revisionStorage->serializeCollection($revisions),
    ] + $default_element_keys;
    $element['revision_history']['#attributes']['class'] = ['revision-history-data'];
    $add_revision_on_submit = $this->collaborationSettings->isRevisionHistoryOnSubmit();
    $element['#attached']['drupalSettings']['ckeditor5Premium']['addRevisionOnSubmit'] = $add_revision_on_submit;

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[] $users_data */
    $users_data = array_merge($comments, $suggestions, $revisions);
    $element['#attached']['drupalSettings']['ckeditor5Premium']['users'] = $this->userDataProvider->getFromEntities($users_data);

    // Add the container for the revision list.
    $element['revision_history_container'] = [
      '#type' => 'container',
      '#weight' => -1,
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
    $form_object = $form_state->getFormObject();
    if (!$this->isFormTypeSupported($form_object)) {
      // Do not process anything, the entity is missing.
      return;
    }

    $entity = $form_object->getEntity();

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $features = [
      'track_changes' => $this->suggestionStorage,
      'comments' => $this->commentsStorage,
      'revision_history' => $this->revisionStorage,
    ];

    foreach ($items as $item_key => $item_parents) {
      $this->dispatchDocumentUpdateEvent($entity, $item_key);

      $suggestion_source_data = $this->getFormElementSourceData($form_state, $item_parents, 'track_changes');
      $suggestion_ids = $this->suggestionStorage->getSuggestionEntityIDs($suggestion_source_data);
      $filter_format = $this->getFormElementFilterFormat($form_state, $item_parents);

      foreach ($features as $key => $storage) {
        $source_data = $this->getFormElementSourceData($form_state, $item_parents, $key);
        if ($storage instanceof CollaborationSuggestionDependingStorageInterface) {
          $storage->setSuggestionIds($suggestion_ids);
        }
        if ($storage instanceof CollaborationContentFilteringStorageInterface) {
          $storage->setSourceFilterFormat($filter_format);
        }
        $entities_data = $storage->processSourceData($source_data, $entity, $item_key);
        $this->doStorageOperations($entities_data, $storage);
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

        // Let's make sure that callback is set only once.
        foreach ($callbacks as $test_callback) {
          if (is_array($test_callback) && in_array('onCompleteFormSubmit', $test_callback)) {
            return;
          }
        }
        $callbacks[] = $submit_callback;
        NestedArray::setValue($form, $key, $callbacks);
      }
    }
  }

  /**
   * Execute the storage commands based on the given markup data.
   *
   * @param array $entities_data
   *   The entities data collected from markup.
   * @param object $storage
   *   The related type storage.
   */
  private function doStorageOperations(array $entities_data, object $storage): void {
    foreach ($entities_data as $element_data) {

      $data_entity = $storage->load($element_data['id']);
      if ($data_entity instanceof EntityInterface) {
        $storage->update($data_entity, $element_data);
      }
      else {
        $storage->add($element_data);
      }
    }
  }

  /**
   * Returns the form element source value array.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param $item_parents
   *   Form item parents.
   * @param $key
   *   Type of the data stored.
   */
  private function getFormElementSourceData(FormStateInterface $form_state, $item_parents, $key): array {
    $source = $form_state->getValue([...$item_parents, $key]);

    return (array) json_decode($source, TRUE);
  }

  /**
   * Returns FilterFormat entity matching value in the selected field.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param array $item_parents
   *   An array describing field values location.
   */
  private function getFormElementFilterFormat(FormStateInterface $form_state, array $item_parents): ?FilterFormatInterface {
    $fieldFormat = $form_state->getValue([...$item_parents, 'format']);

    return $fieldFormat ? FilterFormat::load($fieldFormat) : NULL;
  }

  /**
   * Checks if the passed form object is supported.
   *
   * @param \Drupal\Core\Form\FormInterface $form_object
   *   Form object from the $form_state object.
   */
  private function isFormTypeSupported(FormInterface $form_object): bool {
    return $form_object instanceof EntityFormInterface && $form_object->getEntity() instanceof FieldableEntityInterface;
  }

  /**
   * Dispatches document update event for specified field.
   *
   * @param FieldableEntityInterface $entity
   *   Source entity
   * @param string $key
   *   Key value for source field.
   */
  protected function dispatchDocumentUpdateEvent(FieldableEntityInterface $entity, string $key): void {
    $original = $entity->original;

    $event = new CollaborationEventBase(
      $entity,
      User::load($this->currentUser->id()),
      CollaborationEventBase::DOCUMENT_UPDATED
    );
    $event->setRelatedDocumentKey($key);

    $this->eventDispatcher->dispatch(
      $event,
      CollaborationEventBase::DOCUMENT_UPDATED
    );
  }
}
