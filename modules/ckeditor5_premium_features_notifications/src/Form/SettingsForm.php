<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Form;

use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormBase;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryDefault;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryPluginManager;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderPluginManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration form of the "Export to Word" feature.
 */
class SettingsForm extends SharedBuildConfigFormBase {

  const NOTIFICATION_CONFIG = 'ckeditor5_premium_features_notifications.settings';

  public function __construct(ConfigFactoryInterface $configFactory,
                              protected NotificationMessageFactoryPluginManager $messageFactoryPluginManager,
                              protected NotificationSenderPluginManager $senderPluginManager) {
    parent::__construct($configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.notification_message_factory'),
      $container->get('plugin.manager.notification_sender'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return self::NOTIFICATION_CONFIG;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config($this->getFormId());

    // Collect plugins information.
    $messageFactoryDefinitions = $this->messageFactoryPluginManager->getDefinitions();
    $senderDefinitions = $this->senderPluginManager->getDefinitions();

    $form['message_factory_plugin'] = [
      '#type' => 'select',
      '#title' => 'Message content factory',
      '#description' => $this->t('Choose the plugin responsible for providing the notification messages templates.'),
      '#options' => array_map(function ($value) {
        return $value['label'];
      }, $messageFactoryDefinitions),
      '#default_value' => $config->get('message_factory_plugin'),
    ];
    $form['sender_plugin'] = [
      '#type' => 'select',
      '#title' => 'Message sender',
      '#description' => $this->t('Choose the plugin responsible for sending the notification messages.'),
      '#options' => array_map(function ($value) {
        return $value['label'];
      }, $senderDefinitions),
      '#default_value' => $config->get('sender_plugin'),
    ];

    $form = $this->addNotificationMessagesTabs($form, $form_state);

    $form['additional_info'] = [
      '#markup' => 'The "Message body" field supports tokens that will be dynamically replaced by corresponding values.
      Currently supported tokens relate to Node and User entities, for example [node:title], [node:url], [user:name].<br/>
      For more entities, please check the two sample lists below:',
      'list' => [
        '#theme' => 'item_list',
        '#items' => [
          [
            '#markup' => '<a href="https://www.drupal.org/node/390482#token-node">Node tokens</a>',
          ],
          [
            '#markup' => '<a href="https://www.drupal.org/node/390482#drupal7tokenslist-token-user">User tokens</a>',
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Adds from elements for configuring message templates.
   */
  protected function addNotificationMessagesTabs($form) {
    $config = $this->config($this->getFormId());
    $form['verticaltabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Message types configuration'),
    ];

    foreach (NotificationMessageFactoryDefault::getSupportedMessageTypes() as $messageType => $messageTitle) {
      $groupKey = $messageType . '__tab';
      // Create a grouping element using a fieldset.
      $form[$groupKey] = [
        '#type' => 'details',
        '#title' => $this->t($messageTitle),
        '#group' => 'verticaltabs',
      ];

      $form[$groupKey][$messageType . '__enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable'),
        '#description' => $this->t('Decide whether your system should support this type of notification.'),
        '#default_value' => $config->get($messageType . '__enabled'),
      ];

      $visibility = [
        '#states' => [
          'visible' => [
            ':input[name="' . $messageType . '__enabled"]' => ['checked' => TRUE],
            'and',
            ':input[name="message_factory_plugin"]' => ['value' => 'ck5_notifications_message'],
          ],
          'required' => [
            'input[name="' . $messageType . '__enabled"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form[$groupKey][$messageType . '__subject'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject'),
        '#description' => $this->t('Subject of the email that will be sent to users.'),
        '#default_value' => $config->get($messageType . '__subject'),
      ] + $visibility;

      $messageConfig = $config->get($messageType . '__message');
      $form[$groupKey][$messageType . '__message'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Message body'),
        '#description' => $this->t('Body of the message sent to the users that collaborated on the updated node.'),
        '#default_value' => $messageConfig['value'] ?? '',
        '#format' => $messageConfig['test_format'] ?? 'full_html',
      ] + $visibility;

      if ($additional = $this->getNotificationAdditionalInstruction($messageType)) {
        $form[$groupKey][$messageType . '__additional_help'] = $additional + $visibility;
      }

    return $form;
  }

  /**
   * Returns additional description specific for passed message type.
   *
   * @param $messageType
   *   Type of message.
   *
   * @return array
   *   Render array with additional info.
   */
  protected function getNotificationAdditionalInstruction($messageType): array {
    return match ($messageType) {
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_STATUS => [
        '#type' => 'container',
        'intro' => [
          '#markup' => 'In this notification, you can use additional tokens with suggestion status:',
        ],
        'list' => [
          '#theme' => 'item_list',
          '#items' => [
            [
              '#markup' => '[suggestion:status] - replaced by system event key.',
            ],
            [
              '#markup' => '[suggestion:status-label] - replaced by translatable event user friendly label.',
            ],
          ],
        ],
      ],
      default => [],
    };
  }

}
