<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Event\SuggestionEvent;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class NotificationSuggestionSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface|\Drupal\Core\Logger\LoggerChannel
   */
  protected LoggerChannelInterface $loggerChannel;

  public function __construct(
    protected NotificationSender $notificationSender,
    LoggerChannelFactory $channelFactory,
  ) {
    $this->loggerChannel = $channelFactory->get('notifications');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SuggestionEvent::SUGGESTION_ACCEPT => 'suggestionStatusChange',
      SuggestionEvent::SUGGESTION_DISCARD => 'suggestionStatusChange',
    ];
  }

  public function suggestionStatusChange(SuggestionEvent $event): void {
    if ($event->getSuggestion()->getAuthorId() == $event->getAccount()->id()) {
      return;
    }

    try {
      $parameters = [
        'node' => $event->getSuggestion()->getReferencedEntity(),
        'user' => $event->getAccount(),
        'suggestion' => $event,
      ];
    } catch (\Exception $e) {
      return;
    }

    $recipients = [
      $event->getSuggestion()->getAuthorId(),
    ];

    $this->notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_STATUS,
      $recipients,
      $parameters
    );
  }

}
