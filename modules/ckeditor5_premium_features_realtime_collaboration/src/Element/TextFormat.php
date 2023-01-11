<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Element;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features\Element\Ckeditor5TextFormatInterface;
use Drupal\ckeditor5_premium_features\Element\Ckeditor5TextFormatTrait;
use Drupal\ckeditor5_premium_features\Utility\ApiAdapter;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Ckeditor5ChannelHandlingException;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelInterface;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelStorage;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Utility\CollaborationSettings;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat implements Ckeditor5TextFormatInterface {

  use Ckeditor5TextFormatTrait;

  /**
   * The collaboration config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Channel storage.
   *
   * @var \Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelStorage
   */
  protected ChannelStorage $channelStorage;

  /**
   * Creates the text format element instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\ckeditor5_premium_features_realtime_collaboration\Utility\CollaborationSettings $collaborationSettings
   *   The settings service.
   * @param \Drupal\ckeditor5_premium_features\Utility\ApiAdapter $apiAdapter
   *   The api adapter.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected CollaborationSettings $collaborationSettings,
    protected ApiAdapter $apiAdapter,
  ) {
    $this->channelStorage = $this->entityTypeManager->getStorage(ChannelInterface::ENTITY_TYPE_ID);
  }

  /**
   * {@inheritdoc}
   */
  public function processElement(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $this->generalProcessElement($element, $form_state, $complete_form, $this->collaborationSettings);

    $element_unique_id = CKeditorFieldKeyHelper::getElementUniqueId($element['#id']);
    $element_drupal_id = CKeditorFieldKeyHelper::cleanElementDrupalId($element['#id']);

    $element['presence_list'] = [
      '#type' => 'container',
      '#weight' => -5,
      '#attributes' => [
        'class' => [
          'ck-presence-list-container',
        ],
        'id' => $element_drupal_id . '-value-presence-list-container',
      ],
    ];

    $element['#attached']['drupalSettings']['presenceListCollapseAt'] = $this->collaborationSettings->getPresenceListCollapseAt();

    $form_object = $form_state->getFormObject();

    if ($this->isFormTypeSupported($form_object)) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $form_object->getEntity();

      $channel_id = NestedArray::getValue(
        $form_state->getUserInput(),
        [...$element['#parents'], 'entity_channel']
      ) ?? $this->getChannelId($entity->uuid() . $element_unique_id);

      if (!$entity->isNew()) {
        $channel = $this->handleEntityChannel($entity, $channel_id, $element_unique_id);

        if ($channel instanceof ChannelInterface) {
          $channel_id = $channel->id();
        }
        else {
          throw new Ckeditor5ChannelHandlingException("Problem occured while creating Ckeditor5 Channel Entity");
        }
      }

      $this->apiAdapter->validateLibraryVersion($channel_id);

      $element['entity_channel'] = [
        '#type' => 'hidden',
        '#value' => $channel_id,
      ];
    }
    else {
      $channel_id = $this->getChannelId($element_drupal_id . random_bytes(5));
    }

    $element['#attached']['drupalSettings']['ckeditor5ChannelId'][$element_drupal_id] = $channel_id;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    /** @var \Drupal\ckeditor5_premium_features_realtime_collaboration\Element\TextFormat $service */
    $service = \Drupal::service('ckeditor5_premium_features_realtime_collaboration.element.text_format');
    return $service->processElement($element, $form_state, $complete_form);
  }

  /**
   * {@inheritdoc}
   */
  public static function onCompleteFormSubmit(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\ckeditor5_premium_features_realtime_collaboration\Element\TextFormat $service */
    $service = \Drupal::service('ckeditor5_premium_features_realtime_collaboration.element.text_format');
    $service->completeFormSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function completeFormSubmit(array &$form, FormStateInterface $form_state): void {
    $form_object = $form_state->getFormObject();
    if (!$this->isFormTypeSupported($form_object) || $form_state->isRebuilding()) {
      // Do not process anything, the entity is missing or form is rebuilding.
      return;
    }
    $items = $form_state->get(static::STORAGE_KEY) ?? [];

    $entity = $form_object->getEntity();

    foreach ($items as $element_key => $element_parents) {
      $entity_channel = $form_state->getValue([...$element_parents, 'entity_channel']);
      $this->handleEntityChannel($entity, $entity_channel, $element_key);
    }
  }

  /**
   * Handles creating new Channel entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Referenced entity.
   * @param string $entity_channel
   *   Desired entity channel ID.
   * @param string $element_id
   *   ID of the field element.
   *
   * @return \Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelInterface|null
   *   Channel entity if exists.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function handleEntityChannel(EntityInterface $entity, string $entity_channel, string $element_id): ?ChannelInterface {
    if ($channel = $this->channelStorage->loadByEntity($entity, $element_id)) {
      return $channel;
    }

    try {
      return $this->channelStorage->createChannel($entity, $entity_channel, $element_id);
    }
    catch (EntityStorageException $e) {
      return $this->channelStorage->loadByEntity($entity, $element_id);
    }
  }

  /**
   * Generate unique channel ID value.
   *
   * @param string $uuid
   *   The node uuid.
   *
   * @return string
   *   The channelID.
   */
  private function getChannelId(string $uuid): string {
    return substr(Crypt::hashBase64($uuid), 0, 36);
  }

}
