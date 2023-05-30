<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\ckeditor5_premium_features\Utility\LibraryVersionChecker;
use Drupal\ckeditor5_premium_features\Utility\PluginHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
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
    protected PluginHelper $pluginHelper,
    protected LibraryVersionChecker $libraryVersionChecker,
  ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ...$parent_arguments): static {
    return new static(
      $container->get('ckeditor5_premium_features.plugin_helper'),
      $container->get('ckeditor5_premium_features.core_library_version_checker'),
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
    // A dummy configuration value because of the parent class
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
    $note = $this->t('In order to setup the Real Time Collaboration, use the <a href="@url">global realtime collaboration configuration instead</a>.', [
      '@url' => Url::fromRoute('ckeditor5_premium_features_realtime_collaboration.form.settings')->toString(),
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

    if (in_array('sourceEditing', $toolbars)) {
      $form_state->setErrorByName('editor', $this->t('Source editing can`t be enabled when Realtime Collaboration module is used'));
    }
    if (in_array('commentsArchive', $toolbars) && !$this->libraryVersionChecker->isLibraryVersionHigherOrEqual('37.1.0')) {
      $form_state->setErrorByName('editor', $this->t('Comments archive is enabled with Ckeditor5 version 37.1.0 or higher. Update Drupal to use Comments Archive.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
  }

}
