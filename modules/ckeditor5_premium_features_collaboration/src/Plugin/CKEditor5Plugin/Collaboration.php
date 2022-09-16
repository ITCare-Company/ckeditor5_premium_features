<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface;
use Drupal\ckeditor5_premium_features\Utility\PluginHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Track changes & comments plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class Collaboration extends CKEditor5PluginDefault implements CKEditor5PluginElementsSubsetInterface, ContainerFactoryPluginInterface {
  use CKEditor5PluginConfigurableTrait;

  /**
   * Creates the Track Changes plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param \Drupal\ckeditor5_premium_features\Utility\PluginHelper $pluginHelper
   *   Plugin helper service.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected SettingsConfigHandlerInterface $settingsConfigHandler,
    protected PluginHelper $pluginHelper,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ...$parent_arguments): static {
    return new static(
      $container->get('ckeditor5_premium_features.config_handler.settings'),
      $container->get('ckeditor5_premium_features.plugin_helper'),
      ...$parent_arguments
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    return [
      '<comment-start>',
      '<comment-start name>',
      '<comment-end>',
      '<comment-end name>',
      '<suggestion-start>',
      '<suggestion-start name>',
      '<suggestion-end>',
      '<suggestion-end name>',
    ];
  }

  /**
   * Gets the list of all toolbars related to the collaboration features.
   *
   * @return string[]
   *   The toolbar names.
   */
  public static function getToolbars(): array {
    return [
      'trackChanges',
      'comment',
      'revisionHistory',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    // A dummy configuration value becasue of the parent class
    // which force to have a form related methods
    // in case we want to use `getElementsSubset` method.
    return [
      'enabled' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $note = $this->t('In order to setup the annotation sidebar use the <a href="@url">global collaboration configuration instead</a>.', [
      '@url' => Url::fromRoute('ckeditor5_premium_features_collaboration.form.settings')->toString(),
    ]);
    $form['note'] = [
       ['#markup' => '<p>' . $this->t('The configuration for this plugin is not available.') . '</p>'],
       ['#markup' => '<p>' . $note . '</p>'],
    ];

    // A dummy form element in order to make the submission works.
    $form['enabled'] = [
      '#type' => 'hidden',
      '#default_value' => $this->configuration['enabled'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $toolbars = $this->pluginHelper->getFormToolbars($form_state);

    if (in_array('trackChanges', $toolbars) === TRUE &&
      in_array('comment', $toolbars) === FALSE) {
      $form_state->setErrorByName('editor', 'The Comment plugin has to be enabled when Track Changes plugin is enabled');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {

    /** @var \Drupal\Core\Form\FormState $complete_form_state */
    $complete_form_state = $form_state->getCompleteFormState();

    $toolbars = $this->pluginHelper->getFormToolbars($form_state);

    // Enable filter if any collaboration feature is enabled.
    $has_any_collaboration_feature = (bool) array_intersect($toolbars, static::getToolbars());
    $complete_form_state->setValue([
      'filters',
      'ckeditor5_premium_features_collaboration_filter',
      'status',
    ], $has_any_collaboration_feature);

    // Set the dummy enabled flag on the configuration.
    /** @var \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $definition */
    $definition = $this->getPluginDefinition();
    $toolbar_item = array_key_first($definition->getToolbarItems());
    $status = in_array($toolbar_item, (array) $toolbars, TRUE);

    $this->configuration = [
      'enabled' => $status,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config['licenseKey'] = $this->settingsConfigHandler->getLicenseKey();
    $static_plugin_config['comments']['editorConfig']['extraPlugins'] = [];

    return $static_plugin_config;
  }

}
