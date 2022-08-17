<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface;
use Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class MentionsIntegration {

  /**
   * Creates the mentions integration element instance.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface $editorStorageHandler
   *   The editor storage handler.
   * @param \Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider $userDataProvider
   *   The user data storage.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   */
  public function __construct(
    protected EditorStorageHandlerInterface $editorStorageHandler,
    protected UserDataProvider $userDataProvider,
    protected AccountProxyInterface $currentUser,
    protected CollaborationSettings $collaborationSettings,
  ) { }

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
   */
  public function processElement(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    if (!$this->editorStorageHandler->hasCollaborationFeaturesEnabled($element)) {
      // Don't process as the editor does not have
      // any collaboration features enabled.
      return $element;
    }

    $form_object = $form_state->getFormObject();
    if (!$form_object instanceof EntityFormInterface || !$form_object->getEntity() instanceof EntityInterface) {
      // Do not process anything, the entity is missing.
      return $element;
    }

    if (!$this->currentUser->hasPermission('mention users')) {
      return $element;
    }

    $element['#attached']['drupalSettings']['ckeditor5Premium']['mentions']['minCharacter'] = $this->collaborationSettings->getMentionMinimalCharactersCount();
    $element['#attached']['drupalSettings']['ckeditor5Premium']['mentions']['dropdownLimit'] = $this->collaborationSettings->getMentionAutocompleteListLength();
    $element['#attached']['drupalSettings']['ckeditor5Premium']['mentions']['marker'] = $this->collaborationSettings->getMentionsMarker();

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
    $service = \Drupal::service('ckeditor5_premium_features_collaboration.element.mentions_integration');
    return $service->processElement($element, $form_state, $complete_form);
  }
}
