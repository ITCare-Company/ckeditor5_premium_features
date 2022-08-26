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
   * @param \Drupal\Core\State\StateInterface $state
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
    $type = $message->getType();

    /** @var \Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage $messageQueueStorage */
    $messageQueueStorage = $this->entityTypeManager->getStorage(Message::ENTITY_TYPE_ID);

    foreach ($userIds as $userId) {
      $messageQueueEntity = $messageQueueStorage->getMessageForUserAndDocument($userId, $documentId);

      // dopisz do kolejki danego usear:
//      $messageQueueEntity->appendMessageItem(...)
    }

    return TRUE;
  }

}
