<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration;

use Drupal\Component\Utility\Xss;
use Drupal\filter\FilterFormatInterface;

trait CollaborationFilteringTrait {

  protected FilterFormatInterface $filterFormat;

  public function setSourceFilterFormat(FilterFormatInterface $filterFormat): void {
    $this->filterFormat = $filterFormat;
  }

  public function getAdditionalAllowedTags() {
    return [
      'p',
    ];
  }

  public function filterContent(string $content): string {
    $restrictions = $this->filterFormat->getHtmlRestrictions();

    $allowed_tags = !empty($restrictions['allowed']) ? array_keys($restrictions['allowed']) : Xss::getHtmlTagList();

    $allowed_tags =  array_merge($allowed_tags, $this->getAdditionalAllowedTags());

    return Xss::filter($content, $allowed_tags);
  }

}
