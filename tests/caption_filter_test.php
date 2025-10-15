<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @file
 * Test script for the CaptionFilterController.
 *
 * This script tests the caption filter endpoint by sending a POST request
 * with HTML content containing a data-caption attribute.
 */

// Bootstrap Drupal.
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Client;

$autoloader = require_once 'autoload.php';

// Create a Guzzle HTTP client.
$client = new Client([
  'base_uri' => 'http://localhost/',
  'timeout' => 30,
  'headers' => [
    'Content-Type' => 'application/json',
  ],
]);

// HTML content with an image that has a data-caption attribute.
$content = '<p>This is a test paragraph.</p><img src="/test.jpg" alt="Test image" data-caption="This is a test caption" /><p>Another paragraph.</p>';

try {
  // Send a POST request to the caption filter endpoint.
  $response = $client->post('/ck5/api/caption-filter', [
    'json' => [
      'content' => $content,
    ],
  ]);

  // Get the response body.
  $body = $response->getBody()->getContents();
  $data = Json::decode($body);

  // Output the original and processed content for comparison.
  echo "Original content:\n";
  echo $content . "\n\n";
  echo "Processed content:\n";
  echo $data['content'] . "\n";

  // Check if the caption was applied.
  if (strpos($data['content'], 'class="caption"') !== false) {
    echo "\nSuccess: Caption filter was applied correctly.\n";
  } else {
    echo "\nError: Caption filter was not applied correctly.\n";
  }
} catch (\Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
