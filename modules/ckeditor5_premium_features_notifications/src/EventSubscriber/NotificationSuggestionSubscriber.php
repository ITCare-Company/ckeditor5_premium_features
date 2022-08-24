<?php

namespace Drupal\ckeditor5_premium_features_notifications\EventSubscriber;

use Drupal\ckeditor5_premium_features_collaboration\Event\SuggestionEvent;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Suggestion notification subscriber class.
 */
class NotificationSuggestionSubscriber implements EventSubscriberInterface {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface|\Drupal\Core\Logger\LoggerChannel
   */
  protected LoggerChannelInterface $loggerChannel;

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender $notificationSender
   *   Notification sender service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $channelFactory
   *   Logger factory.
   */
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

  /**
   * Sends notifications.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Event\SuggestionEvent $event
   *   Suggestion event object.
   */
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
    }
    catch (\Exception $e) {
      $this->loggerChannel->error("Suggestion notification error: @error <br /> <br /><pre>@trace</pre>", [
        '@error' => $e->getMessage(),
        '@trace' => $e->getTraceAsString(),
      ]);
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
