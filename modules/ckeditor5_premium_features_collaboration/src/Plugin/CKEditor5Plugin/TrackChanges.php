<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface;
use Drupal\ckeditor5_premium_features_collaboration\Utility\RouteContextEntityTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Track changes plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class TrackChanges extends CKEditor5PluginDefault implements CKEditor5PluginElementsSubsetInterface, ContainerFactoryPluginInterface {
  use CKEditor5PluginConfigurableTrait;
  use RouteContextEntityTrait;

  /**
   * Creates the Track Changes plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected SettingsConfigHandlerInterface $settingsConfigHandler,
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
      ...$parent_arguments
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    return [
      '<comment-start name>',
      '<comment-end name>',
      '<suggestion-start name>',
      '<suggestion-end name>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'sidebar' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['sidebar'] = [
      '#type' => 'select',
      '#title' => $this->t('Annotation sidebar'),
      '#options' => [
        // @todo Define key for automatic mode.
        '' => $this->t('Automatic'),
        'inline' => $this->t('Use inline balloons'),
        'narrowSidebar' => $this->t('Use narrow sidebar'),
        'wideSidebar' => $this->t('Use wide sidebar'),
      ],
      '#default_value' => $this->getConfiguration()['sidebar'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration = $form_state->cleanValues()->getValues();
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config['licenseKey'] = $this->settingsConfigHandler->getLicenseKey();
    $static_plugin_config['sidebar'] = ['inline'];

    if (!isset($static_plugin_config['routeContext'])) {
      $static_plugin_config['routeContext'] = $this->getRouteContext();
    }

    return $static_plugin_config;
  }

}
