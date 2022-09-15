<?php


declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\ckeditor5_premium_features\Utility\PluginHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 realtime plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class Realtime extends CKEditor5PluginDefault implements CKEditor5PluginElementsSubsetInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * Creates the Realtime collaboration plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features\Utility\PluginHelper $pluginHelper
   *   Plugin helper service.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected PluginHelper $pluginHelper, ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ...$parent_arguments): static {
    return new static(
      $container->get('ckeditor5_premium_features.plugin_helper'),
      ...$parent_arguments
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'allowed_tags' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['allowed_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed tags'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $toolbars = $this->pluginHelper->getFormToolbars($form_state);

    if (in_array('sourceEditing', $toolbars)) {
      $form_state->setErrorByName('editor', t('Source editing can`t be enabled when Realtime Collaboration module is used'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    //$static_plugin_config['collaboration']['channelId'] = 'abc';
    $static_plugin_config['revisionHistory']['editorContainer'] = 'sidebar';

    return $static_plugin_config;
  }

}

