<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

interface Ckeditor5DiffInterface {

  public function getDiff(string $oldDocument, string $newDocument): ?string;

  public function getDiffContext(): ?string;
}
