<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Form;

use Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the form for the main module & submodule configuration.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Required length of the Environment ID.
   */
  const ENVIRONMENT_ID_LENGTH = 20;

  /**
   * Required length of the API secret.
   */
  const API_SECRET_LENGTH = 60;

  /**
   * Required length of the License key.
   */
  const LICENSE_KEY_MIN_LENGTH = 48;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface $configHandler
   *   Module settings handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              protected SettingsConfigHandlerInterface $configHandler) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('ckeditor5_premium_features.config_handler.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features.settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      $this->getFormId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $user_input = $form_state->getUserInput();
    $auth_type = $user_input['auth_type'] ?? NULL;

    $form['configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Premium features configuration'),
      '#open' => TRUE,
      '#description' =>
      $this->t("Premium features will only work if configured correctly. If you have not subscribed yet, you can start <a href='@trial'>a free trial</a>.", ['@trial' => 'https://orders.ckeditor.com/trial/premium-features'])
      . '<br>'
        // @todo define the documentation URL.
      . $this->t("Follow the <a href='@documentation'>dedicated documentation for Drupal</a> as most of the steps necessary to run premium features have been already included in this module.", ['@documentation' => 'https://www.drupal.org/project/ckeditor5_premium_features']),
    ];

    $configuration = [];

    $dashboard_url = 'https://dashboard.ckeditor.com/';

    $configuration['license_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('License key'),
      '#description' => $this->t('The license key is required <strong>only</strong> for Revision History, Track changes and Comments (<strong>without</strong> real-time collaboration).'),
    ];

    $configuration['auth_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Authorization type'),
      '#options' => [
        'none' => $this->t('Not set'),
        'key' => $this->t('Access key'),
        'dev_token' => $this->t('Development token'),
      ],
      '#default_value' => 'key',
      '#description' => $this->t('Select the authorization suitable type for your features. The access key-based authorization is highly recommended and the best option in production environment. The development token should rather be used for testing purposes. Required for Export to Word/PDF and Real-time collaboration.')
      . '<br />'
      . $this->t('The access key-based authorization is required for real-time collaboration and optional for Export to Word/PDF to generate documents without the watermark.'),
    ];

    $configuration['env'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment ID'),
      '#required' => $auth_type == 'key',
      '#description' =>
      $this->t('The environment management panel can be found in <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url]),
      '#states' => [
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'key'],
        ],
        'required' => [
          'select[name="auth_type"]' => ['value' => 'key'],
        ],
      ],
    ];

    $configuration['access_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access key'),
      '#required' => $auth_type == 'key',
      '#description' =>
      $this->t('The access key for the environment can be found in the <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url]),
      '#states' => [
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'key'],
        ],
        'required' => [
          'select[name="auth_type"]' => ['value' => 'key'],
        ],
      ],
    ];

    $configuration['dev_token_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Development token URL'),
      '#required' => $auth_type == 'dev_token',
      '#description' => $this->t('The development token URL should be used with care as it does not provide sufficient permission validation. While it is good for testing, it is highly recommended to specify Environment ID and Access Key instead for production environments.'),
      '#attributes' => [
        'placeholder' => 'https://',
      ],
      '#states' => [
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
        'required' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
      ],
    ];

    $configuration['dev_token_accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand the consequences of using a development token URL.'),
      '#states' => [
        'required' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
      ],
    ];

    $configuration['web_socket_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Web Socket URL'),
      '#description' =>
        $this->t('The web socket url can be found in the <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url])
        . '<br>'
        . $this->t('Required for Real-time collaboration.'),
    ];

    $this->setDefaultValues($configuration);

    $form['configuration'] = $configuration + $form['configuration'];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#open' => TRUE,
      '#description' =>
      $this->t('CKEditor Premium Features needs to load additional plugins (“DLLs”) in order to run. By default this module will detect the version of CKEditor your website is running and load required plugins from a CDN automatically.')
      . '<br>'
      . $this->t('Specify the DLL packages location <strong>only</strong> if you host the DLL packages by yourself. Contact us in case of any questions.'),
    ];

    $advanced['dll_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DLL packages location'),
      '#description' => $this->t('Leave this field empty unless you know what you are doing. The path must end by "/"
<br />This field supports additional token: "' .SettingsConfigHandlerInterface::DLL_PATH_VERSION_TOKEN . '" - replaced dynamically with the version of your CKEditor.'),
      '#default_value' => $this->configHandler->getDefaultDllLocation(),
      '#attributes' => [
        'placeholder' => $this->configHandler->getDefaultDllLocation(),
      ],
    ];

    $this->setDefaultValues($advanced);

    $form['advanced'] = $advanced + $form['advanced'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $this->processCleanValues($form_state);

    $access_key = $form_state->getValue('access_key', FALSE);
    $env = $form_state->getValue('env', FALSE);
    $auth_type = $form_state->getValue('auth_type');
    $license_key = $form_state->getValue('license_key');

    // Below is the validation of credential fields value length.
    $length_message = '@name length is invalid (@num characters required)';
    $min_length_message = '@name length is invalid (minimum @num characters required)';

    if (!empty($license_key) && strlen($license_key) < self::LICENSE_KEY_MIN_LENGTH) {
      $form_state->setErrorByName('license_key', $this->t($min_length_message, ['@name' => 'License key', '@num' => self::LICENSE_KEY_MIN_LENGTH]));
    }

    if ($auth_type == 'key') {
      if (!empty($env) && strlen($env) != self::ENVIRONMENT_ID_LENGTH) {
        $form_state->setErrorByName('env', $this->t($length_message, ['@name' => 'Environment ID', '@num' => self::ENVIRONMENT_ID_LENGTH]));
      }

      if (!empty($access_key) && strlen($access_key) != self::API_SECRET_LENGTH) {
        $form_state->setErrorByName('access_key', $this->t($length_message, ['@name' => 'Access key', '@num' => self::API_SECRET_LENGTH]));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config($this->getFormId());
    $clean_values = $this->processCleanValues($form_state);

    // Let's make sure the path ends with the trailing slash.
    if (!empty($clean_values['dll_location'])) {
      $clean_values['dll_location'] = trim($clean_values['dll_location'], ' /') . '/';
    }

    $dll_changed = $config->get('dll_location') !== $clean_values['dll_location'];

    $config
      ->setData($clean_values)
      ->save();

    $invalidate_tags = [
      'ckeditor5_plugins',
      'editor_plugins',
      'filter_plugins',
    ];

    if ($dll_changed) {
      $invalidate_tags[] = 'library_info';
    }

    Cache::invalidateTags($invalidate_tags);

    parent::submitForm($form, $form_state);
  }

  /**
   * Sets the default value on the form elements.
   *
   * It is taking the config value if present.
   *
   * @param array $elements
   *   The form elements to be processed.
   */
  private function setDefaultValues(array &$elements): void {
    $config = $this->config(($this->getFormId()));
    foreach ($elements as $key => $element) {
      $elements[$key]['#default_value'] = $config->get($key) ?? ($element['#default_value'] ?? NULL);
    }
  }

  /**
   * Additionally cleans up the form state values.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object that values should be cleaned up additionally.
   *
   * @return array
   *   Form state clean values.
   */
  protected function processCleanValues(FormStateInterface $form_state): array {
    $clean_values = $form_state->cleanValues()->getValues();

    foreach ($clean_values as &$value) {
      if (is_string($value)) {
        $value = trim($value);
      }
    }
    $form_state->setValues($clean_values);

    return $clean_values;
  }
}
