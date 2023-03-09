<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Element;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features\Utility\CommonCollaborationSettingsInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFormInterface;

/**
 * Trait providing scripts with common preprocessing te input text element.
 */
trait Ckeditor5TextFormatTrait {

  /**
   * Common text element preprocessing.
   *
   * @param array $element
   *   Text element to be processed.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Current form state object.
   * @param array $completeForm
   *   Complete form structure.
   * @param \Drupal\ckeditor5_premium_features\Utility\CommonCollaborationSettingsInterface $commonCollaborationSettings
   *   Settings object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function generalProcessElement(array &$element, FormStateInterface $formState, array &$completeForm, CommonCollaborationSettingsInterface $commonCollaborationSettings): array {
    $elementUniqueId = CKeditorFieldKeyHelper::getElementUniqueId($element['#id']);
    $elementDrupalId = CKeditorFieldKeyHelper::cleanElementDrupalId($element['#id']);
    $idAttribute = 'data-' . Ckeditor5TextFormatInterface::STORAGE_KEY . '-element-id';

    $element['sidebar'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'id' => $elementDrupalId . '-value-presence-list-container',
      ],
    ];

    // Attach annotation sidebar.
    AnnotationSidebar::process($element, $commonCollaborationSettings);

    $formObject = $formState->getFormObject();

    $element['value']["#attributes"]['data-ckeditorfieldid'] = $elementDrupalId;
    $element['value']["#attributes"][$idAttribute] = $elementUniqueId;

    if ($this->isFormTypeSupported($formObject)) {
      $items = $formState->get(Ckeditor5TextFormatInterface::STORAGE_KEY) ?? [];
      $items[$elementUniqueId] = $element['#parents'];
      $formState->set(Ckeditor5TextFormatInterface::STORAGE_KEY, $items);

      // We need to attach the submit just in case the entity was created
      // before the rtc module was enabled.
      self::addCallback('onCompleteFormSubmit', [['#submit']], $completeForm);

      self::addCallback('onValidateForm', [['#validate']], $completeForm);
    }
    else {
      // We still need to process in order to stop our integration from
      // throwing exceptions in console, but we'll block editor toolbar buttons.
      $element['#attached']['drupalSettings']['ckeditor5Premium']['disableCollaboration'] = TRUE;
    }

    // Add the container for the revision list.
    $element['revision_history_container'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'class' => ['revision-history-container-data'],
        $idAttribute => $elementUniqueId,
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
   * Adds the callback to the form.
   *
   * @param string $callbackName
   *   Callback function name.
   * @param array $callbackKeys
   *   Callback keys.
   * @param array $form
   *   The form structure.
   * @param int $nestingCounter
   *   Nesting counter.
   */
  private static function addCallback(string $callbackName, array $callbackKeys, array &$form, int $nestingCounter = 0): void {
    $callback = [static::class, $callbackName];
    foreach ($callbackKeys as $key) {
      if (NestedArray::keyExists($form, $key)) {
        $callbacks = NestedArray::getValue($form, $key) ?? [];

        // Let's make sure that callback is set only once.
        foreach ($callbacks as $test_callback) {
          if (is_array($test_callback) && in_array($callbackName, $test_callback)) {
            return;
          }
        }
        $callbacks[] = $callback;
        NestedArray::setValue($form, $key, $callbacks);
      }
    }

    // Here we are diving in the form to find potential #submit elements that
    // are placed deeper in the form. Such case can be observed using Gin admin
    // theme, which is wrapping action bar in another container.
    foreach ($form as &$element) {
      if (!is_array($element)) {
        continue;
      }

      // Here we are checking if nesting is not too deep to prevent loops,
      // or some unexpected errors with nesting.
      if ($nestingCounter > Ckeditor5TextFormatInterface::NESTING_COUNTER_LIMIT) {
        continue;
      }

      self::addCallback($callbackName, $callbackKeys, $element, $nestingCounter + 1);
    }
  }

  /**
   * Checks if the passed form object is supported.
   *
   * @param \Drupal\Core\Form\FormInterface $formObject
   *   Form object from the $form_state object.
   */
  private function isFormTypeSupported(FormInterface $formObject): bool {
    return $formObject instanceof EntityFormInterface && $formObject->getEntity() instanceof FieldableEntityInterface;
  }

}
