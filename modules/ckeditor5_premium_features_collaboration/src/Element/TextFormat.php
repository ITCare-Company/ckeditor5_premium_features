<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features\Element\Ckeditor5TextFormatInterface;
use Drupal\ckeditor5_premium_features\Element\Ckeditor5TextFormatTrait;
use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationContentFilteringStorageInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityEventDispatcherInterface;
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
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\FilterFormatInterface;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat implements Ckeditor5TextFormatInterface {

  use Ckeditor5TextFormatTrait;

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
   * The array of storages operation to dispatch.
   *
   * @var array
   */
  protected array $storagesOperations;

  /**
   * The array of collaboration features storages.
   *
   * @var array
   */
  protected array $features;

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
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user.
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
    protected StateInterface $state
  ) {
    $this->suggestionStorage = $this->entityTypeManager->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
    $this->commentsStorage = $this->entityTypeManager->getStorage(CommentInterface::ENTITY_TYPE_ID);
    $this->revisionStorage = $this->entityTypeManager->getStorage(RevisionInterface::ENTITY_TYPE_ID);
    $this->features = [
      'track_changes' => $this->suggestionStorage,
      'comments' => $this->commentsStorage,
      'revision_history' => $this->revisionStorage,
    ];
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

    $this->generalProcessElement($element, $form_state, $complete_form, $this->collaborationSettings);

    $form_object = $form_state->getFormObject();
    $entity = NULL;

    if ($this->isFormTypeSupported($form_object)) {
      $entity = $form_object->getEntity();
    }

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

    $storageData = $form_state->get(static::STORAGE_KEY_COLLABORATION) ?? [];

    // Setup the suggestions.
    $suggestions = $entity ? $this->suggestionStorage->loadByEntity($entity, $id) : [];

    $element['value']['#attributes'][$id_attribute] = $id;
    $element['track_changes'] = [
      '#default_value' => $storageData[$id]['track_changes'] ?? $this->suggestionStorage->serializeCollection($suggestions),
    ] + $default_element_keys;
    $element['track_changes']['#attributes']['class'] = ['track-changes-data'];

    // Setup the comments.
    $comments = $entity ? $this->commentsStorage->loadByEntity($entity, $id) : [];

    $element['comments'] = [
      '#default_value' => $storageData[$id]['comments'] ?? $this->commentsStorage->serializeCollection($comments),
    ] + $default_element_keys;
    $element['comments']['#attributes']['class'] = ['comments-data'];

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $items[$id] = $element['#parents'];
    $form_state->set(static::STORAGE_KEY, $items);

    // Setup the revision history.
    $revisions = $entity ? $this->revisionStorage->loadByEntity($entity, $id) : [];

    $element['revision_history'] = [
      '#default_value' => $storageData[$id]['revision_history'] ?? $this->revisionStorage->serializeCollection($revisions),
    ] + $default_element_keys;
    $element['revision_history']['#attributes']['class'] = ['revision-history-data'];
    $add_revision_on_submit = $this->collaborationSettings->isRevisionHistoryOnSubmit();
    $element['#attached']['drupalSettings']['ckeditor5Premium']['addRevisionOnSubmit'] = $add_revision_on_submit;

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface[] $users_data */
    $users_data = array_merge($comments, $suggestions, $revisions);
    $element['#attached']['drupalSettings']['ckeditor5Premium']['users'] = $this->userDataProvider->getFromEntities($users_data);

    return $element;
  }

  /**
   * Process entity form to handle collaboration data after paragraphs collapse.
   *
   * @param array $form
   *   Form to be altered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public static function processFormWithCollaborationStorage(array &$form, FormStateInterface $form_state): void {
    $storage = $form_state->getStorage();

    if (!empty($storage[static::STORAGE_KEY_COLLABORATION])) {
      self::addSubmitCallback($form);
    }
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

    $items = $form_state->get(static::STORAGE_KEY) ?? [];

    $order_switch = $this->detectOrderChange($form_state, $items);
    $this->filterOrderSwitch($order_switch);

    if ($form_state->isRebuilding()) {
      $this->storeEntitiesDataInFormStorage($items, $form_state);

      return;
    }

    $entity = $form_object->getEntity();

    if (!$entity->id()) {
      return;
    }

    foreach ($items as $item_key => $item_parents) {
      $this->processTemporaryStorageRevisionData($form_state, $item_key);

      $source_original_data = $this->getFormElementOriginalValue($form, $item_parents);
      $this->dispatchDocumentUpdateEvent($entity, $item_key, $source_original_data);

      $suggestion_source_data = $this->getFormElementSourceData($form_state, $item_parents, 'track_changes', $item_key);
      $suggestion_ids = $this->suggestionStorage->getSuggestionEntityIDs($suggestion_source_data);
      $filter_format = $this->getFormElementFilterFormat($form_state, $item_parents);

      foreach ($this->features as $key => $storage) {
        $source_data = $this->getFormElementSourceData($form_state, $item_parents, $key, $item_key);

        if (empty($source_data)) {
          continue;
        }

        if ($source_original_data) {
          $storage->setDocumentOriginalValue($source_original_data);
        }
        if ($storage instanceof CollaborationSuggestionDependingStorageInterface) {
          $storage->setSuggestionIds($suggestion_ids);
        }
        if ($storage instanceof CollaborationContentFilteringStorageInterface
            && $filter_format instanceof FilterFormatInterface) {
          $storage->setSourceFilterFormat($filter_format);
        }

        $entities_data = $storage->processSourceData($source_data, $entity, $item_key);
        $this->doStorageOperations($entities_data, $storage, $key);
      }
    }
    if (!empty($order_switch)) {
      $this->changeValuesOrder($order_switch, $entity);
    }
    $this->dispatchStoragesEvents();
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
   * Execute the storage commands based on the given markup data.
   *
   * @param array $entities_data
   *   The entities data collected from markup.
   * @param object $storage
   *   The related type storage.
   */
  private function doStorageOperations(array $entities_data, object $storage, string $storageKey): void {
    $added = [];
    $updated = [];
    foreach ($entities_data as $element_data) {
      $data_entity = $storage->load($element_data['id']);
      if ($data_entity instanceof EntityInterface) {
        $updated[] = [
          'old' => clone $data_entity,
          'new' => $storage->update($data_entity, $element_data),
        ];
      }
      else {
        $added[] = $storage->add($element_data);
      }
    }
    if (!$storage instanceof CollaborationEntityEventDispatcherInterface) {
      return;
    }

    if ($storage instanceof CommentsStorage) {
      $threadIds = [];
      $added = array_filter($added, function ($comment) use (&$threadIds) {
        $uniqueThread = !in_array($comment->getThreadId(), $threadIds);
        if ($uniqueThread) {
          $threadIds[] = $comment->getThreadId();
        }
        return $uniqueThread;
      });
    }

    $this->storagesOperations[$storageKey]['added'] = $added;
    $this->storagesOperations[$storageKey]['updated'] = $updated;
  }

  /**
   * Returns the form element source value array.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param array $item_parents
   *   Form item parents.
   * @param string $key
   *   Type of the data stored.
   * @param string $element_id
   *   ID of the document field.
   *
   * @return array
   *   Returns a decoded array of JSON object.
   */
  private function getFormElementSourceData(FormStateInterface $form_state, array $item_parents, string $key, string $element_id): array {
    $source = $form_state->getValue([...$item_parents, $key]) ?? '';

    if (empty($source)) {
      $storageCollaborationData = $form_state->get(static::STORAGE_KEY_COLLABORATION);

      if (isset($storageCollaborationData[$element_id][$key])) {
        $source = $storageCollaborationData[$element_id][$key];
      }
    }

    return (array) json_decode($source, TRUE);
  }

  /**
   * Additional processing required for handling data stored in form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param string $element_id
   *   ID of the document field.
   */
  private function processTemporaryStorageRevisionData(FormStateInterface $form_state, string $element_id): void {
    $collaboration_storage = $form_state->get(static::STORAGE_KEY_COLLABORATION);

    if (!empty($collaboration_storage[$element_id]['revision_history'])) {
      $source_data = json_decode($collaboration_storage[$element_id]['revision_history'], TRUE);
      foreach ($source_data as &$rev_data) {
        if (empty($rev_data['creatorId'])) {
          $rev_data['attributes']['new_draft_req'] = TRUE;
        }
      }
      $collaboration_storage[$element_id]['revision_history'] = json_encode($source_data);

      $form_state->set(static::STORAGE_KEY_COLLABORATION, $collaboration_storage);
    }
  }

  /**
   * Stores collaboration entities in the form state for later processing.
   *
   * @param array $items
   *   List of document fields info.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  private function storeEntitiesDataInFormStorage(array $items, FormStateInterface $form_state): void {
    $storageData = [];
    foreach ($items as $item_key => $item_parents) {
      foreach ($this->features as $key => $storage) {
        $source_data = $this->getFormElementSourceData($form_state, $item_parents, $key, $item_key);
        if (empty($source_data)) {
          continue;
        }

        $storageData[$item_key][$key] = json_encode($source_data);
      }
    }

    if (!empty($storageData)) {
      $form_state->set(static::STORAGE_KEY_COLLABORATION, $storageData);
    }
  }

  /**
   * Returns an original value set for the element.
   *
   * @param array $form
   *   Form array.
   * @param array $item_parents
   *   Array defining path to the field.
   */
  private function getFormElementOriginalValue(array $form, array $item_parents) {
    $result_path = [];
    foreach (array_chunk($item_parents, 2) as $subArray) {
      $result_path[] = array_shift($subArray);
      $result_path[] = 'widget';
      $result_path[] = reset($subArray);
    }
    $result_path[] = '#default_value';

    return NestedArray::getValue($form, $result_path);
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
   * Dispatches document update event for specified field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Source entity.
   * @param string $key
   *   Key value for source field.
   * @param string|null $original_value
   *   Optional original document value.
   */
  protected function dispatchDocumentUpdateEvent(FieldableEntityInterface $entity, string $key, string $original_value = NULL): void {
    $event = new CollaborationEventBase(
      $entity,
      User::load($this->currentUser->id()),
      CollaborationEventBase::DOCUMENT_UPDATED
    );
    $event->setRelatedDocumentKey($key);
    if (!empty($original_value)) {
      $event->setOriginalContent($original_value);
    }

    $this->eventDispatcher->dispatch(
      $event,
      CollaborationEventBase::DOCUMENT_UPDATED
    );
  }

  /**
   * Detect order changes in form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $items
   *   Items.
   *
   * @return array
   *   Array of keys to swap.
   */
  private function detectOrderChange(FormStateInterface $form_state, array $items): array {
    $field_storage = $form_state->get('field_storage');
    $field_storage_parents = $field_storage['#parents'] ?? [];

    $change_order = [];

    foreach ($items as $itemKey => $field_parents) {
      $newElementId = $this->getOriginalParentsPath($field_parents, $field_storage_parents);

      if ($newElementId !== NULL && $newElementId != $itemKey) {
        $change_order[$itemKey] = $newElementId;
      }
    }

    return $change_order;
  }

  /**
   * Get orginal parent path.
   *
   * @param array $parentsPath
   *   Parent path.
   * @param array $fieldsStorage
   *   Fields storage.
   *
   * @return string|null
   *   Element id or null.
   */
  private function getOriginalParentsPath(array $parentsPath, array $fieldsStorage): ?string {
    $processedParents = [];
    $wasModifiedDelta = FALSE;
    for ($currentKey = 0; $currentKey < count($parentsPath); $currentKey++) {
      $parent = $parentsPath[$currentKey];
      if (!isset($parentsPath[$currentKey + 1]) || ($parentsPath[$currentKey + 1] !== 0 && (int) $parentsPath[$currentKey + 1] == 0)) {
        $processedParents[] = $parent;
        continue;
      }

      $currentDelta = $parentsPath[$currentKey + 1];
      $oldDelta = NestedArray::getValue(
        $fieldsStorage,
          [...array_slice($parentsPath, 0, $currentKey),
            '#fields', $parent,
            'original_deltas',
            $currentDelta,
          ]);

      $processedParents[] = $parent;
      if ($oldDelta === NULL || $oldDelta === $currentDelta) {
        continue;
      }
      $wasModifiedDelta = TRUE;
      $processedParents[] = $oldDelta;
      ++$currentKey;
    }

    if ($wasModifiedDelta) {
      $newElementId = 'edit-' . implode('-', $processedParents);
      return CKeditorFieldKeyHelper::getElementUniqueId($newElementId);
    }

    return NULL;
  }

  /**
   * Swaps key attributes in collaboration entities.
   *
   * @param array $idsToSwap
   *   Array with ids to swap.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  private function changeValuesOrder(array $idsToSwap, EntityInterface $entity) {
    foreach ($idsToSwap as $firstId => $secondId) {
      foreach ($this->features as $storage) {
        $firstValues = $storage->loadByEntity($entity, $firstId);
        $secondValues = $storage->loadByEntity($entity, $secondId);
        $this->swapKeyAttribute($firstValues, $secondId);
        $this->swapKeyAttribute($secondValues, $firstId);
      }
    }
  }

  /**
   * Changes key attribute in collaboration entities.
   *
   * @param array $values
   *   Array of the collaboration entities.
   * @param string $key
   *   New key.
   */
  private function swapKeyAttribute(array $values, string $key) {
    if (!empty($values)) {
      foreach ($values as $collaborationEntity) {
        $collaborationEntity->setKey($key);
        $collaborationEntity->save();
      }
    }
  }

  /**
   * Remove duplicates from orderSwitch array.
   *
   * @param array $orderSwitch
   *   Array with ids to swap.
   */
  private function filterOrderSwitch(array &$orderSwitch) {
    $swapIds = [];
    foreach ($orderSwitch as $key => $value) {
      if (!isset($swapIds[$key]) && !isset($swapIds[$value])) {
        $swapIds[$key] = $value;
      }
    }
    $orderSwitch = $swapIds;
  }

  /**
   * Dispatch collaboration storages events.
   */
  protected function dispatchStoragesEvents(): void {
    foreach ($this->features as $key => $storage) {
      if (empty($this->storagesOperations[$key])) {
        continue;
      }
      $operations = $this->storagesOperations[$key];
      $added = $operations['added'] ?? [];
      $updated = $operations['updated'] ?? [];
      if ($added) {
        foreach ($added as $added_entity) {
          $storage->dispatchNewEntity($added_entity);
        }
      }
      if ($updated) {
        foreach ($updated as $upd_info) {
          $storage->dispatchUpdatedEntity($upd_info['old'], $upd_info['new']);
        }
      }
    }
  }

}
