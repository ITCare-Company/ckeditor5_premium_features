<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage;
use Drupal\ckeditor5_premium_features_notifications\Entity\Message;
use Drupal\ckeditor5_premium_features_notifications\Entity\MessageStorage;
use Drupal\ckeditor5_premium_features_notifications\Entity\MessageInterface;
use Drupal\ckeditor5_premium_features_notifications\Entity\MessageItemInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderMailBulk;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Entity\User;
use Drupal\Core\Render\RendererInterface;

class BulkMessageSender {

  use StringTranslationTrait;

  /**
   * The suggestion storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionStorage
   */
  protected SuggestionStorage $suggestionStorage;

  /**
   * The comments storage.
   *
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentsStorage
   */
  protected CommentsStorage $commentsStorage;

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
    $this->suggestionStorage = $this->entityTypeManager->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
    $this->commentsStorage = $this->entityTypeManager->getStorage(CommentInterface::ENTITY_TYPE_ID);
    $this->messageStorage = $this->entityTypeManager->getStorage(MessageInterface::ENTITY_TYPE_ID);
  }

  /**
   * @param $message
   *   Message entity.
   * @return String
   *   Return rendered body of message.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function prepareContent(Message $message): String {
    $messageItems = $message->getItems(); //message entity id
    $body = [];

    foreach ($messageItems as $messageItem) {
      $messageType = $messageItem->getType();
      $title = NULL;

      /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Comment $relatedEntity */
      $relatedEntity = $messageItem->getRelatedEntity();
      switch ($messageType) {

        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_DEFAULT:
          $body[$messageItem->id()] = [
            '#theme' => 'notification_context',
            '#context' => [],
            '#title' => 'Document updated',
            '#thread' => [
              [
                '#theme' => 'notification_thread_default',
                '#item' => $messageItem,
              ],
            ]
          ];
          break;

        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED:
        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_THREAD_REPLY:
          $threadID = $relatedEntity->getThreadId();
          $context = $messageItem->getMessageContent();

          $query="//comment-start[contains(@name,'$threadID')]";

          $body[$messageItem->id()] = [
            '#theme' => 'notification_context',
            '#context' => [],
            '#title' => 'Comment added',
          ];

          foreach ($this->getHighlightedContext($context, $query) as $markup) {
            $fixedMarkup = str_replace('</comment-start>', '', $markup);
            $fixedMarkup = str_replace('<comment-end', '<span', $fixedMarkup);
            $fixedMarkup = str_replace('</comment-end>', '</comment-start>', $fixedMarkup);

            $body[$messageItem->id()]['#context'][] = [
              '#markup' =>  $fixedMarkup,
              '#allowed_tags' => array_merge(Xss::getAdminTagList(), [
                'comment-start',
                'comment-end',
              ]),
            ];
          }

          $body[$messageItem->id()]['#thread'] = $this->renderThread($messageItem);

          break;


        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_STATUS:
          $relatedSuggestion = $relatedEntity;
          $title = $title ?? 'Suggestion status update: ' . $relatedSuggestion->getAttributes()['status'];

        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_REPLY:
          /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Suggestion $suggestion */
          $relatedSuggestion = $relatedSuggestion ?? $this->suggestionStorage->load($relatedEntity->getThreadId());
          $title = $title ?? 'Suggestion reply';

        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_ADDED:
          $suggestionChain = $relatedSuggestion ? $relatedSuggestion->getChain() : $relatedEntity->getChain();
          $context = $messageItem->getMessageContent();

          $queryOrParts = [];
          /** @var SuggestionInterface $suggestion */
          foreach ($suggestionChain as $suggestion) {
            $chainSuggestionId = $suggestion->id();
            $queryOrParts[] = "contains(@name,'$chainSuggestionId')";
          }
          $query='//suggestion-start[' . implode(' or ', $queryOrParts) . ']';

          $body[$messageItem->id()] = [
            '#theme' => 'notification_context',
            '#context' => [],
            '#title' => $title ?? 'Suggestion added',
          ];

          foreach ($this->getHighlightedContext($context, $query) as $markup) {
            $fixedMarkup = str_replace('</suggestion-start>', '', $markup);
            $fixedMarkup = str_replace('<suggestion-end', '<span', $fixedMarkup);
            $fixedMarkup = str_replace('</suggestion-end>', '</suggestion-start>', $fixedMarkup);

            $body[$messageItem->id()]['#context'][] = [
              '#markup' =>  $fixedMarkup,
              '#allowed_tags' => array_merge(Xss::getAdminTagList(), [
                'suggestion-start',
                'suggestion-end',
              ]),
            ];
          }

          $body[$messageItem->id()]['#thread'] = $this->renderThread($messageItem);

          break;

        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_COMMENT:
          $threadID = $relatedEntity->getThreadId();
          $context = $messageItem->getMessageContent();

          $query="//comment-start[contains(@name,'$threadID')]";

          $body[$messageItem->id()] = [
            '#theme' => 'notification_context',
            '#context' => [],
            '#title' => 'Comment mention',
          ];

          foreach ($this->getHighlightedContext($context, $query) as $markup) {
            $fixedMarkup = str_replace('</comment-start>', '', $markup);
            $fixedMarkup = str_replace('<comment-end', '<span', $fixedMarkup);
            $fixedMarkup = str_replace('</comment-end>', '</comment-start>', $fixedMarkup);

            $body[$messageItem->id()]['#context'][] = [
              '#markup' =>  $fixedMarkup,
              '#allowed_tags' => array_merge(Xss::getAdminTagList(), [
                'comment-start',
                'comment-end',
              ]),
            ];
          }

          $body[$messageItem->id()]['#thread'] = [
              '#theme' => 'notification_thread_comment',
              '#comment' => $relatedEntity,
          ];

          break;

        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT:
          $context = $messageItem->getMessageContent();
          $userName = $message->getUser()->getAccountName();
          $mentionMarker = '#' . $userName;
          $query = "//span[@data-mention=\"$mentionMarker\"]";

          $body[$messageItem->id()] = [
            '#theme' => 'notification_context',
            '#context' => [],
            '#title' => 'Document mention',
          ];

          foreach ($this->getHighlightedContext($context, $query) as $markup) {
            $body[$messageItem->id()]['#context'][] = [
              '#markup' => $markup,
            ];
          }

          break;
      }

      $messageItem->delete();
    }

    $messageOuterWrapper = [
      '#theme' => 'notification_message',
      '#title' => 'Document "'  . $message->getTitle() . '" recent activities',
      '#items' => $body,
    ];

    return (String)$this->renderer->renderPlain($messageOuterWrapper);
  }

  /**
   * Returns matching HTML elements list with addition class used for highlighting matched element.
   *
   * @param $context
   *   Source HTML content.
   * @param $query
   *   XPATH query that will be used for selecting matching HTML part.
   *
   * @return array
   */
  protected function getHighlightedContext($context, $query): array {
    $document = Html::load($context);

    $contextParts = [];

    $xpath = new \DOMXPath( $document);
    $matchingElements = $xpath->query($query);
    if (!empty($matchingElements)) {
      /** @var \DOMElement $element */
      foreach ($matchingElements as $element) {
        $element->setAttribute('class', $element->getAttribute('class') . ' highlight-item');

        $contextParts[] = $element->ownerDocument->saveXML($element->parentNode);
      }
    }

    return $contextParts;
  }

  /**
   * Callback from cron job.
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function sendBulkMails() {
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
   * @param String $title
   *   Title of message.
   * @param String $body
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

  /**
   * Renders message item related thread.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Entity\MessageItemInterface $messageItem
   *
   * @return array
   */
  protected function renderThread(MessageItemInterface $messageItem): array {
    $result = [];
    /** @var CommentInterface $threadItem */
    foreach ($messageItem->getThread() as $threadItem) {
      $result[] = [
        '#theme' => 'notification_thread_comment',
        '#comment' => $threadItem,
      ];
    }

    return $result;
  }
}
