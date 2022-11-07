<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features_notifications\Entity\Message;
use Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage;
use Drupal\ckeditor5_premium_features_notifications\Entity\MessageInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderMailBulk;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Entity\User;
use Drupal\Core\Render\RendererInterface;

class BulkMessageSender {

  use StringTranslationTrait;

  /**
   * The message storage.
   *
   * @var \Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage
   */
  protected MessageStorage $messageStorage;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   Mail manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager,
                              protected MailManagerInterface $mailManager,
                              protected RendererInterface $renderer,
                              protected NotificationSettings $notificationSettings) {
    $this->messageStorage = $this->entityTypeManager->getStorage(MessageInterface::ENTITY_TYPE_ID);
  }

  /**
   * @param $message
   *   Message entity.
   *
   * @return string
   *   Return rendered body of message.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function prepareContent(Message $message): string {
    /** @var NotificationMessageFactoryInterface $messageFactory */
    $messageFactory = $this->notificationSettings->getMessageFactoryPlugin();

    $messageItems = $message->getItems(); //message entity id
    $body = [];

    foreach ($messageItems as $messageItem) {
      /** @var \Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageInterface $messageContent */
      $messageContent = $messageFactory->getMessage($messageItem->getType(), $messageItem->getEvent());
      $messageBodyArray = $messageContent->getMessageBody();

      $body[$messageItem->id()] = [
        '#theme' => 'notification_context',
        '#messageContent' => [
          '#markup' => implode('', $messageBodyArray),
          '#allowed_tags' => NotificationContextHelper::getNotificationAllowedTags(),
        ],
      ];

      $messageItem->delete();
    }

    $messageOuterWrapper = [
      '#theme' => 'notification_message_bulk',
      '#title' => $this->t('Document "@title" recent activities', [
        '@title' => $message->getTitle()
      ]),
      '#items' => $body,
    ];

    return (String)$this->renderer->renderPlain($messageOuterWrapper);
  }

  /**
   * Callback from cron job.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function sendBulkMails(): void {
    $messages = $this->messageStorage->getOldestMessages(
      10,
      $this->notificationSettings->getBulkNotificationsInterval()
    );

    foreach ($messages as $message) {
      $user = $message->getUser();
      $body = $this->prepareContent($message);

      if (!empty($body)) {
        $this->sendMail($message->getTitle(), [$body], $user);
      }

      $message->set('sent', 1);
      $message->save();

      $this->messageStorage->cleanMessageItems($message);
    }
  }

  /**
   *
   * @param string $title
   *   Title of message.
   * @param array $body
   *  Body of message.
   * @param User $user
   *   User entity.
   *
   * @return void
   */
  private function sendMail(string $title, array $body, User $user): void {
    $params["subject"] = $title;
    $params["body"] = $body;

    $this->mailManager->mail(
      "ckeditor5_premium_features_notifications",
      NotificationSenderMailBulk::BULK_MAIL_TYPE,
      $user->getEmail(),
      NULL,
      $params
    );
  }

}
