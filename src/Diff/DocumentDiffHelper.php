<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Diff;

use Drupal\ckeditor5_premium_features\Plugin\Filter\FilterCollaboration;
use Drupal\filter\FilterPluginManager;

/**
 * This class checks if document is changed.
 */
class DocumentDiffHelper {

  /**
   * Collaboration filter.
   *
   * @var \Drupal\ckeditor5_premium_features\Plugin\Filter\FilterCollaboration
   */
  protected FilterCollaboration $filterCollaboration;

  /**
   * Constructor.
   *
   * @param \Drupal\ckeditor5_premium_features\Diff\Ckeditor5DiffInterface $ckeditor5Diff
   *   Ckeditor5 diff service.
   * @param \Drupal\filter\FilterPluginManager $filterPluginManager
   *   Filter plugin manager.
   */
  public function __construct(
    protected Ckeditor5DiffInterface $ckeditor5Diff,
    FilterPluginManager $filterPluginManager,
  ) {
    $this->filterCollaboration = $filterPluginManager->createInstance('ckeditor5_premium_features_collaboration_filter');
  }

  /**
   * Check if the document without collaboration tags changed.
   *
   * @param string $originalData
   *   Original document data.
   * @param string $newData
   *   New document data.
   *
   * @return bool
   *   Is document changed.
   */
  public function isRawDocumentChanged(string $originalData, string $newData): bool {
    $originalDataWithoutCollaborationTags = $this->filterCollaboration->process($originalData, NULL)->getProcessedText();
    $originalNewDataWithoutCollaborationTags = $this->filterCollaboration->process($newData, NULL)->getProcessedText();
    if (!empty($source_original_data)) {
      // Check if a raw document without the collaboration tags is changed.
      if ($this->ckeditor5Diff->getDiff($originalDataWithoutCollaborationTags, $originalNewDataWithoutCollaborationTags)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
