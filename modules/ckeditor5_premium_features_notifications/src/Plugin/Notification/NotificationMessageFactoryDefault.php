<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_notifications\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default class used for notification_messages_factory plugins.
 */
class NotificationMessageFactoryDefault extends PluginBase implements NotificationMessageFactoryInterface, ContainerFactoryPluginInterface {

  protected $config;

  public function __construct(array $configuration,
                              $pluginId,
                              $pluginDefinition,
                              ConfigFactoryInterface $configFactory,
                              protected Token $tokenService) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->config = $configFactory->get(SettingsForm::NOTIFICATION_CONFIG);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('token'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // The title from YAML file discovery may be a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  public function getMessage(string $messageType, array $parameters): NotificationMessageInterface {
    switch ($messageType) {
      case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_COMMENT:
      case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT:
      case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_REPLY:
      case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_STATUS:
      case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_THREAD_REPLY:
      default:
        $subjectTemplate = $this->config->get('subject');
        $bodyTemplate = $this->config->get('message')['value'];
    }

    $subject = $this->tokenService->replace($subjectTemplate, $parameters);
    $body = $this->tokenService->replace($bodyTemplate, $parameters);

    return new NotificationMessage($messageType, $subject, $body);
  }

  /**
   * Returns list of supported message types.
   */
  public static function getSupportedMessageTypes() :array {
    return [
      self::CKEDITOR5_MESSAGE_DEFAULT,
      self::CKEDITOR5_MESSAGE_MENTION_COMMENT,
      self::CKEDITOR5_MESSAGE_MENTION_DOCUMENT,
      self::CKEDITOR5_MESSAGE_THREAD_REPLY,
      self::CKEDITOR5_MESSAGE_SUGGESTION_REPLY,
      self::CKEDITOR5_MESSAGE_SUGGESTION_STATUS,
    ];
  }

}
