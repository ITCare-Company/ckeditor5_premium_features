<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Event;

use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Session\AccountInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

class SuggestionEvent extends Event {

  use TranslatorTrait;

  const SUGGESTION_ACCEPT = 'ck5_collaboration_suggestion_accept';
  const SUGGESTION_DISCARD = 'ck5_collaboration_suggestion_discard';

  public function __construct(protected SuggestionInterface $suggestion,
                              protected AccountInterface $account,
                              protected string $eventType) { }

  /**
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface
   */
  public function getSuggestion(): SuggestionInterface {
    return $this->suggestion;
  }

  /**
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion
   */
  public function setSuggestion(SuggestionInterface $suggestion): void {
    $this->suggestion = $suggestion;
  }

  /**
   * @return \Drupal\Core\Session\AccountInterface
   */
  public function getAccount(): AccountInterface {
    return $this->account;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function setAccount(AccountInterface $account): void {
    $this->account = $account;
  }

  /**
   * @return string
   */
  public function getEventType(): string {
    return $this->eventType;
  }

  /**
   * @param string $eventType
   */
  public function setEventType(string $eventType): void {
    $this->eventType = $eventType;
  }

  /**
   * Returns label for specified event type.
   *
   * @param string $eventType
   *   Type of event.
   *
   * @throws \Exception
   *   Exception if type is not supported.
   */
  public static function getEventLabel(string $eventType): string {
    $supportedTypes = [
      self::SUGGESTION_ACCEPT => 'accepted',
      self::SUGGESTION_DISCARD => 'rejected',
    ];
    if (!isset($supportedTypes[$eventType])) {
      throw new \Exception('Unsupported event type');
    }

    return $supportedTypes[$eventType];
  }
}
