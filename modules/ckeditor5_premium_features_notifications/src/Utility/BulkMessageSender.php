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
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Render\RendererInterface;

class BulkMessageSender {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;


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
   * @var \Drupal\ckeditor5_premium_features_collaboration\Entity\MessageStorage
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
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              MailManagerInterface $mailManager, RendererInterface $renderer) {
    $this->entityTypeManager = $entityTypeManager;
    $this->mailManager = $mailManager;
    $this->renderer = $renderer;
    $this->suggestionStorage = $this->entityTypeManager->getStorage(SuggestionInterface::ENTITY_TYPE_ID);
    $this->commentsStorage = $this->entityTypeManager->getStorage(CommentInterface::ENTITY_TYPE_ID);
    $this->messageStorage = $this->entityTypeManager->getStorage(MessageInterface::ENTITY_TYPE_ID);
  }

  /**
   * @param String $entityId
   *   Id of thread.
   * @return array
   *   Return render array of thread.
   */
  private function prepareThreadReply(String $entityId): array {
    $thread = [];
    $commentThread = $this->commentsStorage->getCommentTree($entityId);
    foreach ($commentThread as $threadItem) {
      $comment = $this->commentsStorage->load($threadItem);

      $thread[$comment->id()] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => 'thread-reply-item',
        ],
        '#value' => $comment->getAuthor()->getDisplayName() . '' . gmdate('Y-m-d H:i:s', $comment->getCreatedTime()),
        'child' => [
          '#markup' => $comment->getContent(),
        ],
      ];
    }

    return $thread;
  }

  /**
   * @param String $content
   *   Given content to catch context.
   * @param String $startTag
   *   Comment tag start.
   * @param String $endTag
   *   Comment tag end.
   * @return array
   *   Return render array of context.
   */
  private function getContext(String $content, String $startTag, String $endTag): array {
    $begin = strpos($content, $startTag);
    if ($begin) {
      $end = strpos($content, $endTag);
      $context = substr($content, $begin, $end - $begin);

      return [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => 'thread-reply-context',
        ],
        'child' => [
          '#markup' => $context,
        ],
      ];
    }
    return [];
  }

  /**
   * @param $comment
   *   The comment entity.
   * @return array
   *   Return render array of mention in thread headline.
   */
  private function getThreadMentionHeadline($comment): array {

    return ['#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => 'thread-mention-headline',
      ],
      'child' => [
        '#markup' => $comment->getAuthor()->getDisplayName() . t(' mention you in thread:'),
      ],
    ];
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
      $entityId = $messageItem->getRelatedEntityId();
      $messageType = $messageItem->getType();
      $title = NULL;

      /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\Comment $relatedEntity */
      $relatedEntity = $messageItem->getRelatedEntity();
      switch ($messageType) {

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

//        case NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_SUGGESTION_STATUS:
//          $suggestion = $this->suggestionStorage->load($entityId);
//          if ($messageItem->get('event_type')->getString() == 'ck5_collaboration_comment_added') {
//            break;
//          }
//          $context = $this->entityTypeManager->getStorage(\Drupal\ckeditor5_premium_features_notifications\Entity\MessageItemInterface::ENTITY_TYPE_ID)->loadByProperties(
//            [
//              'entity_id' => $entityId,
//              'event_type' => 'ck5_collaboration_suggestion_added',
//            ]
//          );
//          $context = reset($context);
//          $suggestionType = $suggestion->getType();
//          $context = $context->get('message_content')->getString();
//          $startTag = '<suggestion-start name="' . $suggestionType .':' . $entityId;
//          $endTag = '<suggestion-end name="' . $suggestionType .':' . $entityId;
//          $body[$messageItem->Id()]['context'] = $this->getContext($context, $startTag, $endTag);
//          if ($suggestion->hasComments()) {
//            $comment = $this->commentsStorage->loadByProperties(['thread_id' => $entityId]);
//            $comment = reset($comment);
//            $body[$messageItem->Id()][] = $this->prepareThreadReply($comment->id());
//          }
//          break;

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

  protected function getHighlightedContext($context, $query, $untilQuery = NULL): array {
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
    $messages = $this->messageStorage->getOldestMessages(10);

    foreach ($messages as $message) {
      $user = $message->getUser();
      $body = $this->prepareContent($message);

      if (!empty($body)) {
        $this->sendMail($message->getTitle(), [$body], $user);
      }

      $message->set('sent', 1);
      $message->save();
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
