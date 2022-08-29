<?php

namespace Drupal\ckeditor5_premium_features_notifications\Plugin\Notification;

use Drupal\ckeditor5_premium_features_notifications\Entity\Message;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for sending notifications through mail.
 */
class NotificationSenderMailBulk extends NotificationSenderBase implements ContainerFactoryPluginInterface {

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   *
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              protected EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function send(NotificationMessageInterface $message, array $userIds): bool|array {
    $documentId = $message->getSourceEvent()->getRelatedDocument()->id();
    $documentType = $message->getSourceEvent()->getRelatedDocument()->getEntityTypeId();
    $type = $message->getType();

    /** @var \Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage $messageQueueStorage */
    $messageQueueStorage = $this->entityTypeManager->getStorage(Message::ENTITY_TYPE_ID);

    foreach ($userIds as $userId) {
      /** @var Message $messageQueueEntity */
      $messageQueueEntity = $messageQueueStorage->getMessageForUserAndDocument($userId, $documentId);
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

}
