<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Element;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Entity\ChannelInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFormInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat {

  public const STORAGE_KEY = 'ckeditor5-premium';

  /**
   * The collaboration config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Creates the text format element instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   *   The user data storage.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    ConfigFactoryInterface               $config_factory
  ) {
    $this->config = $config_factory->getEditable('ckeditor5_premium_features_realtime_collaboration.settings');
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
    $elementUniqueId = CKeditorFieldKeyHelper::getElementUniqueId($element['#id']);
    $elementDrupalId = CKeditorFieldKeyHelper::cleanElementDrupalId($element['#id']);
    $id_attribute = 'data-' . static::STORAGE_KEY . '-element-id';

    $element['presence_list'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'class' => [
          'ck-presence-list-container',
        ],
        'id' => $elementDrupalId . '-value-presence-list-container',
      ],
    ];

    $element['sidebar'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'id' => $elementDrupalId . '-value-presence-list-container',
      ],
    ];
    $element['#attached']['drupalSettings']['presenceListCollapseAt'] = $this->config->get('presence_list_collapse_at') ?? 8;
    // Attach annotation sidebar.
    AnnotationSidebar::process($element, $form_state, $complete_form);

    $form_object = $form_state->getFormObject();

    $element['value']["#attributes"]['data-ckeditorfieldid'] = $elementDrupalId;
    $element['value']["#attributes"][$id_attribute] = $elementUniqueId;

    if ($this->isFormTypeSupported($form_object)) {
      $entity = $form_object->getEntity();

      if ($entity->isNew()) {
        $this->addSubmitCallback($complete_form);
      }
      $entityChannel = $complete_form["channel_id"]["#value"];
      $element['#attached']['drupalSettings']['ckeditor5ChannelId'][$elementDrupalId] =
        $this->getChannelId($entityChannel . $elementDrupalId);
    } else {
      // We still need to process in order to stop our integration from
      // throwing exceptions in console, but we'll block editor toolbar buttons.
      $element['#attached']['drupalSettings']['ckeditor5Premium']['disableCollaboration'] = TRUE;
      $element['#attached']['drupalSettings']['ckeditor5ChannelId'][$elementDrupalId] =
        $this->getChannelId($elementDrupalId . random_bytes(5));
    }

    // Add the container for the revision list.
    $element['revision_history_container'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'class' => ['revision-history-container-data'],
        $id_attribute => $elementUniqueId,
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
    /** @var \Drupal\ckeditor5_premium_features_realtime_collaboration\Element\TextFormat $service */
    $service = \Drupal::service('ckeditor5_premium_features_realtime_collaboration.element.text_format');
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
    /** @var \Drupal\ckeditor5_premium_features_realtime_collaboration\Element\TextFormat $service */
    $service = \Drupal::service('ckeditor5_premium_features_realtime_collaboration.element.text_format');
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
    $channelId = $form_state->getValue('channel_id');

    $this->entityTypeManager->getStorage(ChannelInterface::ENTITY_TYPE_ID)
      ->create([
        'id' => $channelId,
        'entity_type' => $entity->getEntityTypeId(),
        'entity_id' => $entity->uuid(),
        'created' => time(),
      ])->save();
  }

  /**
   * Generate unique channel ID value.
   *
   * @param String $uuid
   *   The node uuid.
   *
   * @return string
   *   The channelID.
   */
  private function getChannelId(String $uuid): string {
    return substr(Crypt::hashBase64($uuid), 0, 36);
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
}
