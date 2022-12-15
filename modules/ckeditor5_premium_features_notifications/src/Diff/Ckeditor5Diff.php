<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationContextHelper;

/**
 * Ckeditor5 helper class for detecting document changes.
 */
class Ckeditor5Diff implements Ckeditor5DiffInterface {

  /**
   * String representing recently processed document with all changes marked.
   *
   * @var string
   */
  protected string $context;

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features_notifications\Utility\NotificationContextHelper $contextHelper
   *   Context detecting helper service.
   */
  public function __construct(
    protected NotificationContextHelper $contextHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getDiff(string $oldDocument, string $newDocument): ?string {
    $htmlDiff = new Ckeditor5HtmlDiff($oldDocument, $newDocument);
    $htmlDiff->getConfig()
      ->setPurifierEnabled(FALSE);

    $this->context = $htmlDiff->build();

    return $htmlDiff->getAddedContent();
  }

  /**
   * {@inheritdoc}
   */
  public function getDiffAddedContext(): ?string {
    $highlights = $this->contextHelper->getDocumentChangesContext($this->context, TRUE);

    return implode('<div class="spacer">...</div>', $highlights);
  }

  /**
   * {@inheritdoc}
   */
  public function getDiffContext(): ?string {
    $highlights = $this->contextHelper->getDocumentChangesContext($this->context);

    return implode('<div class="spacer">...</div>', $highlights);
  }

}
