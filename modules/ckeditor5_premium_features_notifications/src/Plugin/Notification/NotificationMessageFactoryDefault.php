<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_notifications\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default class used for notification_messages_factory plugins.
 */
class NotificationMessageFactoryDefault extends PluginBase implements NotificationMessageFactoryInterface, ContainerFactoryPluginInterface {

  /**
   * Notification config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  public function __construct(array $configuration,
                              $pluginId,
                              $pluginDefinition,
                              ConfigFactoryInterface $configFactory,
                              protected Token $tokenService) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->config = $configFactory->get(SettingsForm::NOTIFICATION_CONFIG);
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function getMessage(string $messageType, array $parameters): NotificationMessageInterface|NULL {
    if (!self::isMessageTypeSupported($messageType)) {
      return NULL;
    }

    $subject = $this->tokenService->replace($this->getMessageSubject($messageType), $parameters);
    $body = $this->tokenService->replace($this->getMessageBody($messageType), $parameters);

    return new NotificationMessage($messageType, $subject, $body);
  }

  /**
   * Returns list of supported message types with their labels.
   */
  public static function getSupportedMessageTypes(): array {
    return [
      self::CKEDITOR5_MESSAGE_DEFAULT => 'Default (to be removed)',
      self::CKEDITOR5_MESSAGE_MENTION_COMMENT => 'Mentioned in a comment',
      self::CKEDITOR5_MESSAGE_MENTION_DOCUMENT => 'Mentioned in a document',
      self::CKEDITOR5_MESSAGE_THREAD_REPLY => 'Reply in a thread',
      self::CKEDITOR5_MESSAGE_SUGGESTION_REPLY => 'Reply to a suggestion',
      self::CKEDITOR5_MESSAGE_SUGGESTION_STATUS => 'Suggestion status change',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function isMessageTypeSupported(string $messageType): bool {
    $supportedTypes = self::getSupportedMessageTypes();
    return isset($supportedTypes[$messageType]);
  }

  /**
   * Returns subject template for specified message type.
   *
   * @param string $messageType
   *   Type of message.
   */
  protected function getMessageSubject(string $messageType): string {
    return $this->config->get($messageType . '__subject');
  }

  /**
   * Returns body template for specified message type.
   *
   * @param string $messageType
   *   Type of message.
   */
  protected function getMessageBody(string $messageType): string {
    return $this->config->get($messageType . '__message')['value'];
  }
}
