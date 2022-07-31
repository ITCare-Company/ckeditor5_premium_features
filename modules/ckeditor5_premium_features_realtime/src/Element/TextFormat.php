<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime\Element;


use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat {

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
    $this->config = $config_factory->getEditable('ckeditor5_premium_features_realitme.settings');
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

    $node = $form_state->getFormObject()->getEntity();
    $id = $node->id();

    $element['presence_list'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'class' => [$id . $element["#id"]],
        'id' => $element["#id"] . '-value-presence-list-container',
      ],
    ];

    $element['sidebar'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'class' => [$id . $element["#id"]],
        'id' => $element["#id"] . '-value-presence-list-containersss',
      ],
    ];

    $default_element_keys = [
      '#type' => 'textarea',
      '#attributes' => [
        // The admin theme may vary, so this is the safest solution.
        'style' => 'display: none;',
        $id_attribute => $id,
      ],
      '#theme_wrappers' => [],
    ];
    //$element['#attached']['drupalSettings']['ckeditor5Premiumrealtime']['channelId'] = '12345678910';

    // Attach annotation sidebar.
    AnnotationSidebar::process($element, $form_state, $complete_form);

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
    $service = \Drupal::service('ckeditor5_premium_features_realtime.element.text_format');
    return $service->processElement($element, $form_state, $complete_form);
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
