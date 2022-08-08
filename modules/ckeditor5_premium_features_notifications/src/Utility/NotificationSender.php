<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\Core\Database\Connection;
use Drupal\Core\Mail\MailManagerInterface;

class NotificationSender {

  const NOTIFICATION_OPT_OUT_FIELD_TABLE = 'user__field_ck5_premium_notifications';
  const NOTIFICATION_OPT_OUT_FIELD_VALUE = 'field_ck5_premium_notifications_value';

  public function __construct(protected Connection $dbConnection, protected MailManagerInterface $mailManager) { }

  /**
   * Sends notification mail.
   *
   * @param array $recipientIds
   * @param array $parameters
   *
   * @return bool|array
   */
  public function sendNotification(array $recipientIds, array $parameters): bool|array {
    $mails = $this->getUserMails($recipientIds);

    if (empty($mails)) {
      return FALSE;
    }

    $mainMail = array_pop($mails);
    if (count($mails) > 0) {
      $parameters['cc'] = $mails;
    }

    return $this->mailManager->mail(
      'ckeditor5_premium_features_notifications',
      'content_updated',
      $mainMail,
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

    $userMailQuery = $this->dbConnection->select('users_field_data', 'u')
      ->fields('u', ['mail']);

    if ($this->dbConnection->schema()->tableExists(self::NOTIFICATION_OPT_OUT_FIELD_TABLE)) {
      $userMailQuery->join(self::NOTIFICATION_OPT_OUT_FIELD_TABLE, 'n', 'u.uid = n.entity_id');
      $userMailQuery->condition(self::NOTIFICATION_OPT_OUT_FIELD_VALUE, 1);
    }

    return $userMailQuery->condition('uid', $userIds, 'IN')
      ->execute()
      ->fetchCol();
  }
}
