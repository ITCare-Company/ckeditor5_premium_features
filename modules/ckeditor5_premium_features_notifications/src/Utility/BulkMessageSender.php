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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Render\RendererInterface;

class BulkMessageSender {

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
    $thread = [];
    $body = [];
    foreach ($messageItems as $messageItem) {
      $entityId = $messageItem->get('entity_id')->getString();
      $messageType = $messageItem->get('message_type')->getString();
      switch ($messageType) {
        case 'ckeditor5_message_thread_reply':
        case 'ckeditor5_message_mention_comment':

          $context = $messageItem->get('message_content')->getString();
          $comment = $this->commentsStorage->load($entityId);
          if ($messageType == 'ckeditor5_message_mention_comment') {
            $body[$messageItem->Id()]['headline'] = $this->getThreadMentionHeadline($comment);
          }
          $threadId = $comment->get('thread_id')->getString();
          $startTag = 'comment-start name="' . $threadId;
          $endTag = 'comment-end name="' . $threadId;
          $body[$messageItem->Id()]['context'] = $this->getContext($context, $startTag, $endTag);
          $body[$messageItem->Id()][] = $this->prepareThreadReply($entityId);

          break;

        case 'ckeditor5_message_suggestion_reply' :
          $comment = $this->commentsStorage->load($entityId);
          $suggestion = $this->suggestionStorage->load($comment->getThreadId());
          $context = $messageItem->get('message_content')->getString();
          $startTag = '<suggestion-start name="insertion:' . $entityId;
          $endTag = '<suggestion-end name="insertion:' . $entityId;
          $body[$messageItem->Id()]['context'] = $this->getContext($context, $startTag, $endTag);
          $body[$messageItem->Id()][] = $this->prepareThreadReply($entityId);

          break;

        case 'ckeditor5_message_suggestion_status' :
          $suggestion = $this->suggestionStorage->load($entityId);
          if ($messageItem->get('event_type')->getString() == 'ck5_collaboration_comment_added') {
            break;
          }
          $context = $this->entityTypeManager->getStorage(\Drupal\ckeditor5_premium_features_notifications\Entity\MessageItemInterface::ENTITY_TYPE_ID)->loadByProperties(
            [
              'entity_id' => $entityId,
              'event_type' => 'ck5_collaboration_suggestion_added',
            ]
          );
          $context = reset($context);
          $suggestionType = $suggestion->getType();
          $context = $context->get('message_content')->getString();
          $startTag = '<suggestion-start name="' . $suggestionType .':' . $entityId;
          $endTag = '<suggestion-end name="' . $suggestionType .':' . $entityId;
          $body[$messageItem->Id()]['context'] = $this->getContext($context, $startTag, $endTag);
          if ($suggestion->hasComments()) {
            $comment = $this->commentsStorage->loadByProperties(['thread_id' => $entityId]);
            $comment = reset($comment);
            $body[$messageItem->Id()][] = $this->prepareThreadReply($comment->id());
          }
          break;

        case 'ckeditor5_message_mention_document' :
          $node = Node::load($messageItem->get('entity_id')->getString());
          $body[$messageItem->id()] = "You are mentioned in document: <a href=\"ckeditor5.localhost/node/" . $node->id() ."/edit\">" . $node->getTitle() . "</a>" . "\n";

          break;
      }

      $messageItem->delete();
    }

    return (String)$this->renderer->renderPlain($body);
  }

  /**
   * Callback from cron job.
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function sendBulkMails() {
  $messages = $this->messageStorage->getOldestMessages(10);
  $title = 'Title of message';
    foreach ($messages as $message) {
      $user = $message->getUser();
      $body = $this->prepareContent($message);
      $this->sendMail($title, $body, $user);
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
  private function sendMail(String $title,String $body, User $user): void {

    $params["context"]["subject"] = $title;
    $params["context"]["message"] = $body;

    $this->mailManager->mail("system", "mail", $user->getEmail(), 'pl', $params);
  }
}
