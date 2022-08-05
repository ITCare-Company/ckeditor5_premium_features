<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class MentionsIntegration {

  /**
   * The collaboration config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Creates the mentions integration element instance.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Storage\EditorStorageHandlerInterface $editorStorageHandler
   *   The editor storage handler.
   * @param \Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider $userDataProvider
   *   The user data storage.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   */
  public function __construct(
    protected EditorStorageHandlerInterface $editorStorageHandler,
    protected UserDataProvider              $userDataProvider,
    ConfigFactoryInterface                  $configFactory,
    protected AccountProxyInterface         $currentUser,
  ) {
    $this->config = $configFactory->get('ckeditor5_premium_features_collaboration.settings');
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

    $element['#attached']['drupalSettings']['ckeditor5Premium']['mentions']['minCharacter'] = $this->config->get('mention_min_character') ?? 1;
    $element['#attached']['drupalSettings']['ckeditor5Premium']['mentions']['dropdownLimit'] = $this->config->get('mention_dropdown_limit') ?? 4;
    $element['#attached']['drupalSettings']['ckeditor5Premium']['mentions']['marker'] = $this->config->get('mention_marker') ?? '#';

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
