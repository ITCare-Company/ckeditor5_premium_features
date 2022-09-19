<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Event;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityBase;
use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

/**
 * Suggestion event class.
 */
class CollaborationEventBase extends Event {

  use TranslatorTrait;

  const DOCUMENT_UPDATED = 'ck5_collaboration_document_updated';
  const COMMENT_ADDED = 'ck5_collaboration_comment_added';
  const SUGGESTION_ACCEPT = 'ck5_collaboration_suggestion_accept';
  const SUGGESTION_DISCARD = 'ck5_collaboration_suggestion_discard';
  const SUGGESTION_ADDED = 'ck5_collaboration_suggestion_added';

  /**
   * Key property that describes the related field ID.
   *
   * @var string
   */
  protected string $relatedDocumentKey;

  /**
   * Collaboration event constructor.
   *
   * @param ContentEntityBase $relatedEntity
   *   Event entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Event account.
   * @param string $eventType
   *   Type of event.
   */
  public function __construct(protected ContentEntityBase $relatedEntity,
                              protected AccountInterface $account,
                              protected string $eventType) {}

  /**
   * Returns event related entity.
   */
  public function getRelatedEntity(): ContentEntityBase {
    return $this->relatedEntity;
  }

  /**
   * Sets related entity property.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $relatedEntity
   */
  public function setRelatedEntity(ContentEntityBase $relatedEntity): void {
    $this->relatedEntity = $relatedEntity;
  }

  /**
   * Returns related document. It can be the same as getRelatedEntity result
   * for some events.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|NULL
   */
  public function getRelatedDocument(): ContentEntityBase|NULL {
    $relatedEntity = $this->getRelatedEntity();

    if (!$relatedEntity instanceof CollaborationEntityBase) {
      return $relatedEntity;
    }

    try {
      return $relatedEntity->getReferencedEntity();
    }
    catch (\Exception) { }

    return NULL;
  }

  /**
   * Returns "key" attribute from the related collaboration entity or NULL if not found.
   */
  public function getRelatedDocumentFieldId(): string|null {
    $relatedEntity = $this->getRelatedEntity();

    if (!$relatedEntity instanceof CollaborationEntityBase) {
      return NULL;
    }

    return $relatedEntity->getKey();
  }

  /**
   * Returns content of the proper field from related content entity.
   */
  public function getRelatedDocumentContent(): string|NULL {
    $relatedDocument = $this->getRelatedDocument();
    $fieldId = $this->relatedDocumentKey ?? $this->getRelatedDocumentFieldId();

    if (!$fieldId) {
      return NULL;
    }

    $fields = $relatedDocument->getFields();

    foreach ($fields as $fieldName => $field) {
      $values = $relatedDocument->get($fieldName)->getValue();
      foreach ($values as $delta => $val) {
        $id = CKeditorFieldKeyHelper::getElementId('edit-' . $fieldName . '-' . $delta);
        if ($fieldId == $id) {
          return $val['value'];
        }
      }
    }

    return NULL;
  }

  /**
   * Setter for the related document "key" property.
   */
  public function setRelatedDocumentKey(string $key): void {
    $this->relatedDocumentKey = $key;
  }

  /**
   * Returns authors of the related content entity.
   *
   * @param bool $filterEventAuthor
   *   Flag if the current user should be filtered out of the list of users.
   */
  public function getRelatedDocumentAuthors(bool $filterEventAuthor = true): array {
    $relatedDocument = $this->getRelatedDocument();

    $authors = [];
    if (method_exists($relatedDocument, 'getOwner')) {
      $authors[] = $relatedDocument->getOwner()->id();
    }
    elseif ($relatedDocument->hasField('uid')) {
      $authors[] = $relatedDocument->get('uid')->getString();
    }

    if ($filterEventAuthor) {
      $authors = array_diff($authors, [$this->getAccount()->id()]);
    }

    return $authors;
  }

  /**
   * Returns event account.
   */
  public function getAccount(): AccountInterface {
    return $this->account;
  }

  /**
   * Sets event account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account.
   */
  public function setAccount(AccountInterface $account): void {
    $this->account = $account;
  }

  /**
   * Returns event type.
   */
  public function getEventType(): string {
    return $this->eventType;
  }

  /**
   * Sets event type.
   *
   * @param string $eventType
   *   Type of the event.
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
