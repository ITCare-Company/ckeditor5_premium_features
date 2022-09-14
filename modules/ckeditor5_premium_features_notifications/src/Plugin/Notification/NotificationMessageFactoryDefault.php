<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSettings;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default class used for notification_messages_factory plugins.
 */
class NotificationMessageFactoryDefault extends PluginBase implements NotificationMessageFactoryInterface, ContainerFactoryPluginInterface {

  public function __construct(array $configuration,
                              $pluginId,
                              $pluginDefinition,
                              protected NotificationSettings $notificationSettings,
                              protected Token $tokenService) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ckeditor5_premium_features_notifications.notification_settings'),
      $container->get('token'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // The title from YAML file discovery may be a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage(string $messageType, CollaborationEventBase $event): NotificationMessageInterface|NULL {
    if (!self::isMessageTypeSupported($messageType)) {
      return NULL;
    }

    try {
      $parameters = $this->getMessageParameters($messageType, $event);
    }
    catch (\Exception) {
      return NULL;
    }

    $subject = $this->tokenService->replace($this->notificationSettings->getMessageSubject($messageType), $parameters);
    $body = $this->tokenService->replace($this->notificationSettings->getMessageBody($messageType), $parameters);

    return new NotificationMessage(
      $messageType,
      $subject,
      $body,
      $event
    );
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
   * @param string $messageType
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\CollaborationEventBase $event
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getMessageParameters(string $messageType, CollaborationEventBase $event): array {
    $parameters = [
      'user' => $event->getAccount(),
    ];

    if ($messageType == self::CKEDITOR5_MESSAGE_DEFAULT) {
      $parameters[$event->getRelatedEntity()->getEntityTypeId()] = $event->getRelatedEntity();
    }

    switch ($messageType) {
      case self::CKEDITOR5_MESSAGE_THREAD_REPLY:
      case self::CKEDITOR5_MESSAGE_SUGGESTION_REPLY:
        /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityBase $collaborationEntity */
        $collaborationEntity = $event->getRelatedEntity();
        $parameters[$collaborationEntity->getEntityTypeTargetId()] = $collaborationEntity->getReferencedEntity();
        break;
    }

    if ($messageType == self::CKEDITOR5_MESSAGE_SUGGESTION_STATUS) {
      $parameters['suggestion'] = $event;
    }

    return $parameters;
  }

}
