<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationContextHelper;
use Drupal\Core\Render\Renderer;

class Ckeditor5Diff implements Ckeditor5DiffInterface {

  protected string $context;

  public function __construct(
    protected NotificationContextHelper $contextHelper,
    protected Renderer $renderer
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
