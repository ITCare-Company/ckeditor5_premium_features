<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorageInterface;
use Drupal\ckeditor5_premium_features_collaboration\Service\MarkupDataProviderInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

class TextFormat {

  public const STORAGE_KEY = 'ckeditor5-premium';

  public static function process(&$element, FormStateInterface $form_state, &$complete_form): array {
    static::addSubmitCallback($complete_form);

    $id = static::getElementId();
    $id_attribute = 'data-' . static::STORAGE_KEY . '-element-id';

    $content = $element['#default_value'] ?? '';
    $users = static::getDataProvider()->getSuggestionsUsers($content);
    $suggestions_ids = static::getDataProvider()->getSuggestionsIds($content);
    $suggestions = static::getSuggestionStorage()->loadMultiple($suggestions_ids);

    $suggestions_data = [];
    foreach ($suggestions as $suggestion) {
      $suggestions_data[] = $suggestion->toArray();
    }

    $element['#attached']['drupalSettings']['ckeditor5Premium']['users'] = $users;

    $element['value']['#attributes'][$id_attribute] = $id;
    $element['track_changes'] = [
      // @todo change to hidden once develompent will be finished.
      '#type' => 'textarea',
      '#title' => t('Track changes'),
      '#attributes' => [
        'class' => [
          'track-changes-data',
        ],
        $id_attribute => $id,
      ],
      '#default_value' => Json::encode($suggestions_data),
    ];

    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $items[$id] = $element['#parents'];
    $form_state->set(static::STORAGE_KEY, $items);

    return $element;
  }

  public static function onCompleteFormSubmit(array &$form, FormStateInterface $form_state) {
    $entity = $form_state->getFormObject()->getEntity();
    if (!$entity instanceof EntityInterface) {
      return;
    }
    $items = $form_state->get(static::STORAGE_KEY) ?? [];
    $storage = static::getSuggestionStorage();
    foreach ($items as $item_parents) {
      $track_changes = $form_state->getValue([...$item_parents, 'track_changes']);
      $items = (array) Json::decode($track_changes);
      foreach ($items as $item) {
       $suggestion = $storage->load($item['id']);
       if ($suggestion instanceof SuggestionInterface) {
         $storage->update($suggestion, $item);
       }
       else {
         $item['entity_type'] = $entity->getEntityTypeId();
         $item['entity_id'] = $entity->id();
         $storage->add($item);
       }
      }

    }
  }

  private static function addSubmitCallback(&$form): void {
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

  private static function getElementId(): string {
    $id = 'id-' . Crypt::randomBytesBase64(8);

    return Html::getId($id);
}

  private static function getDataProvider(): MarkupDataProviderInterface {
    return \Drupal::service('ckeditor5_premium_features_collaboration.makrup_data_provider');
  }

  private static function getSuggestionStorage(): SuggestionStorageInterface {
    return \Drupal::entityTypeManager()->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
  }

}
