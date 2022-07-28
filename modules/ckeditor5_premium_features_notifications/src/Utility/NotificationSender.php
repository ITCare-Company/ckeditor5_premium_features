<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

class NotificationSender {

  public function __construct(protected $dbConnection, protected $mailManager) { }

  /**
   * Sends notification mail.
   *
   * @param array $recipientIds
   * @param array $parameters
   *
   * @return bool
   */
  public function sendNotification(array $recipientIds, array $parameters): bool {
    $mails = $this->getUserMails($recipientIds);

    if (empty($mails)) {
      return FALSE;
    }

    return $this->mailManager->mail(
      'ckeditor5_premium_features_notifications',
      'content_updated',
      implode(', ', $mails),
      NULL,
      $parameters,
      NULL,
      TRUE
    );
  }

  /**
   * Returns a list of user emails.
   *
   * @param array $userIds
   *
   * @return array
   */
  protected function getUserMails(array $userIds): array {
    if (empty($userIds)) {
      return [];
    }

    return $this->dbConnection->select('users_field_data', 'u')
      ->fields('u', ['mail'])
      ->condition('uid', $userIds, 'IN')
      ->execute()
      ->fetchCol();
  }
}
