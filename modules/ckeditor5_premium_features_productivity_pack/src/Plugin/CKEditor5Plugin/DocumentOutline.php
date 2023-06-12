<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_productivity_pack\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Productivity Pack Document Outline Plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class DocumentOutline extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

  const DRUPAL_PLUGIN_NAME = 'ckeditor5_premium_features_productivity_pack_document_outline';

  /**
   * Creates the plugin instance.
   *
   * @param string $featuredPluginId
   *   The id of the feature plugin.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected string $featuredPluginId,
    protected ConfigFactoryInterface $configFactory,
    ...$parent_arguments) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $config = $plugin_definition->toArray()['drupal']['productivity_pack'];

    return new static(
      $config['plugin'],
      $container->get('config.factory'),
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $settings = $editor->getSettings();
    if (empty($settings['plugins'][$this->pluginId]['enabled'])) {
      $static_plugin_config['removePlugins'] = [
        'DocumentOutline',
      ];
    }
    return $static_plugin_config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'enabled' => FALSE,
    ];
  }

  /**
   * Gets the featured plugin id.
   *
   * @return string
   *   The CKEditor plugin name.
   */
  public function getFeaturedPluginId(): string {
    return $this->featuredPluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Document Outline'),
      '#default_value' => $this->configuration['enabled'] ?? FALSE,
      '#description' => t('Enable Document Outline in the editor'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $formValues = $form_state->getValues();
    $isEnabled = $formValues['enabled'] ?? FALSE;
    $this->configuration['enabled'] = (bool) $isEnabled;
  }

}
