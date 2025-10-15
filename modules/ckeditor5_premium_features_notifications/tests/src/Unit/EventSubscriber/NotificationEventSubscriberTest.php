<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

namespace Drupal\Tests\ckeditor5_premium_features_notifications\Unit\EventSubscriber;

use Drupal\ckeditor5_premium_features\Event\CollaborationEventBase;
use Drupal\ckeditor5_premium_features\Utility\Collaborators;
use Drupal\ckeditor5_premium_features_notifications\EventSubscriber\NotificationCommentSubscriber;
use Drupal\ckeditor5_premium_features_notifications\EventSubscriber\NotificationDocumentMentionSubscriber;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryInterface;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageFactoryPluginManager;
use Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderPluginManager;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSettings;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests the notification event subscribers.
 *
 * @group ckeditor5_premium_features_notifications
 * @coversDefaultClass \Drupal\ckeditor5_premium_features_notifications\EventSubscriber\NotificationCommentSubscriber
 * @coversDefaultClass \Drupal\ckeditor5_premium_features_notifications\EventSubscriber\NotificationDocumentMentionSubscriber
 */
class NotificationEventSubscriberTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * The notification comment subscriber.
   *
   * @var \Drupal\ckeditor5_premium_features_notifications\EventSubscriber\NotificationCommentSubscriber
   */
  protected $commentSubscriber;

  /**
   * The notification document mention subscriber.
   *
   * @var \Drupal\ckeditor5_premium_features_notifications\EventSubscriber\NotificationDocumentMentionSubscriber
   */
  protected $documentMentionSubscriber;

  /**
   * The notification sender service mock.
   *
   * @var \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationSender|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $notificationSender;

  /**
   * The collaborators service mock.
   *
   * @var \Drupal\ckeditor5_premium_features\Utility\Collaborators|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $collaboratorsService;

  /**
   * The current user mock.
   *
   * @var \Drupal\Core\Session\AccountInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $currentUser;

  /**
   * The state service mock.
   *
   * @var \Drupal\Core\State\StateInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create mocks for the services needed by the subscribers.
    $this->notificationSender = $this->prophesize(NotificationSender::class);
    $this->collaboratorsService = $this->prophesize(Collaborators::class);
    $this->currentUser = $this->prophesize(AccountInterface::class);
    $this->state = $this->prophesize(StateInterface::class);

    // Create the comment subscriber.
    $this->commentSubscriber = new NotificationCommentSubscriber(
      $this->notificationSender->reveal(),
      $this->collaboratorsService->reveal(),
      $this->currentUser->reveal(),
      $this->state->reveal()
    );

    // Additional mocks needed for document mention subscriber.
    $notificationSettings = $this->prophesize(NotificationSettings::class);
    $filterPluginManager = $this->prophesize(\Drupal\filter\FilterPluginManager::class);
    $mentionsIntegrator = $this->prophesize(\Drupal\ckeditor5_premium_features\Utility\MentionsIntegrator::class);
    $ckeditor5Diff = $this->prophesize(\Drupal\ckeditor5_premium_features\Diff\Ckeditor5DiffInterface::class);

    // Create the document mention subscriber.
    $this->documentMentionSubscriber = new NotificationDocumentMentionSubscriber(
      $this->notificationSender->reveal(),
      $this->collaboratorsService->reveal(),
      $this->currentUser->reveal(),
      $ckeditor5Diff->reveal(),
      $notificationSettings->reveal(),
      $filterPluginManager->reveal(),
      $mentionsIntegrator->reveal()
    );
  }

  /**
   * Tests that the comment subscriber is registered for the correct events.
   */
  public function testCommentSubscriberEvents() {
    $events = NotificationCommentSubscriber::getSubscribedEvents();
    $this->assertArrayHasKey(CollaborationEventBase::COMMENT_ADDED, $events);
    $this->assertEquals('commentAdded', $events[CollaborationEventBase::COMMENT_ADDED]);
  }

  /**
   * Tests that the document mention subscriber is registered for the correct events.
   */
  public function testDocumentMentionSubscriberEvents() {
    $events = NotificationDocumentMentionSubscriber::getSubscribedEvents();
    $this->assertArrayHasKey(CollaborationEventBase::DOCUMENT_UPDATED, $events);
    $this->assertEquals('documentUpdated', $events[CollaborationEventBase::DOCUMENT_UPDATED]);
  }

  /**
   * Tests the comment added notification.
   */
  public function testCommentAddedNotification() {
    // Create a mock event.
    $event = $this->prophesize(CollaborationEventBase::class);

    // Mock a comment entity.
    $comment = $this->prophesize(\Drupal\ckeditor5_premium_features_collaboration\Entity\Comment::class);
    $comment->isReply()->willReturn(FALSE);

    // Set up the event to return our mock comment.
    $event->getRelatedEntity()->willReturn($comment->reveal());

    // Mock document authors.
    $authors = ['1', '2'];
    $event->getRelatedDocumentAuthors()->willReturn($authors);

    // Set up the notification sender to expect a call with the correct parameters.
    $this->notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED,
      $authors,
      $event->reveal()
    )->shouldBeCalled();

    // Call the method being tested.
    $this->commentSubscriber->commentAdded($event->reveal());
  }

  /**
   * Tests the document mention notification.
   */
  public function testDocumentMentionNotification() {
    // Create a mock event.
    $event = $this->prophesize(CollaborationEventBase::class);

    // Mock the mentions detection.
    $this->collaboratorsService->getDocumentMentions()->willReturn(['user1', 'user2']);
    $this->collaboratorsService->getUserIdsByNames(['user1', 'user2'])->willReturn(['1', '2']);

    // Set up the notification sender to expect a call with the correct parameters.
    $this->notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_MENTION_DOCUMENT,
      ['1', '2'],
      $event->reveal()
    )->shouldBeCalled();

    // Call the method being tested.
    $this->documentMentionSubscriber->documentUpdated($event->reveal());
  }

  /**
   * Tests that email bodies are correctly generated.
   */
  public function testEmailBodyGeneration() {
    // Create a database connection mock.
    $connection = $this->prophesize(Connection::class);
    $connection->schema()->willReturn((object) ['tableExists' => function() { return FALSE; }]);

    // Create notification settings mock.
    $notificationSettings = $this->prophesize(NotificationSettings::class);
    $notificationSettings->isMessageEnabled(NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED)
      ->willReturn(TRUE);
    $notificationSettings->getMessageFactoryPlugin()->willReturn($this->createMessageFactoryMock());
    $notificationSettings->getSenderPluginId()->willReturn('test_mail_collector');

    // Create plugin managers.
    $senderPluginManager = $this->prophesize(NotificationSenderPluginManager::class);
    $senderPluginManager->hasDefinition('test_mail_collector')->willReturn(TRUE);
    $senderPluginManager->createInstance('test_mail_collector')->willReturn($this->createMailSenderMock());

    $messageFactoryPluginManager = $this->prophesize(NotificationMessageFactoryPluginManager::class);

    // Create a notification sender with our mocks.
    $notificationSender = new NotificationSender(
      $connection->reveal(),
      $notificationSettings->reveal(),
      $senderPluginManager->reveal(),
      $messageFactoryPluginManager->reveal()
    );

    // Create a mock event.
    $event = $this->prophesize(CollaborationEventBase::class);

    // Test sending a notification.
    $result = $notificationSender->sendNotification(
      NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED,
      ['1', '2'],
      $event->reveal()
    );

    $this->assertTrue($result);
  }

  /**
   * Creates a mock message factory.
   */
  protected function createMessageFactoryMock() {
    $messageFactory = $this->prophesize(NotificationMessageFactoryInterface::class);

    // Create a mock message.
    $message = $this->prophesize(\Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationMessageInterface::class);
    $message->getType()->willReturn(NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED);
    $message->getMessageTitle()->willReturn('Test Notification');
    $message->getMessageBody()->willReturn(['Line 1 of the message', 'Line 2 of the message']);
    $message->getSourceEvent()->willReturn($this->prophesize(CollaborationEventBase::class)->reveal());

    $messageFactory->getMessage(NotificationMessageFactoryInterface::CKEDITOR5_MESSAGE_COMMENT_ADDED,
      \Prophecy\Argument::any())->willReturn($message->reveal());

    return $messageFactory->reveal();
  }

  /**
   * Creates a mock mail sender.
   */
  protected function createMailSenderMock() {
    $mailSender = $this->prophesize(\Drupal\ckeditor5_premium_features_notifications\Plugin\Notification\NotificationSenderInterface::class);
    $mailSender->send(\Prophecy\Argument::any(), \Prophecy\Argument::any())->willReturn(TRUE);
    return $mailSender->reveal();
  }

}
