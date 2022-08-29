<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_notifications\Entity\Message;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for sending notifications through mail.
 */
class NotificationSenderMailBulk extends NotificationSenderBase implements ContainerFactoryPluginInterface {

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $loggerChannel;

  /**
   * {@inheritdoc }
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $channelFactory
   *   Logger factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              protected EntityTypeManagerInterface $entityTypeManager,
                              LoggerChannelFactory $channelFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->loggerChannel = $channelFactory->get('notifications');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function send(NotificationMessageInterface $message, array $userIds): bool|array {
    $documentId = $message->getSourceEvent()->getRelatedDocument()->id();
    $documentType = $message->getSourceEvent()->getRelatedDocument()->getEntityTypeId();

    try {

      /** @var \Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage $messageQueueStorage */
      $messageQueueStorage = $this->entityTypeManager->getStorage(Message::ENTITY_TYPE_ID);

      foreach ($userIds as $userId) {
        /** @var Message $messageQueueEntity */
        $messageQueueEntity = $messageQueueStorage->getMessageForUserAndDocument($userId, $documentId, $documentType);
        if (!$messageQueueEntity) {
          $messageQueueEntity = $messageQueueStorage->createMessage($userId, $documentId, $documentType);

          if (!$messageQueueEntity) {
            continue;
          }
          $messageQueueEntity->save();
        }
        $messageQueueEntity->appendItem(
          $message->getSourceEvent()->getRelatedEntity()->getEntityTypeId(),
          $message->getSourceEvent()->getRelatedEntity()->id(),
          $message->getType(),
          $message->getSourceEvent()->getEventType(),
        );
      }

      return TRUE;
    }
    catch (\Exception $e) {
      $this->loggerChannel->error("Suggestion notification sending error: @error <br /> <br /><pre>@trace</pre>", [
        '@error' => $e->getMessage(),
        '@trace' => $e->getTraceAsString(),
      ]);
    }

    return FALSE;
  }

}
