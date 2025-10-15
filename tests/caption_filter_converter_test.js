/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * Test script for the caption filter converter.
 *
 * This script tests the caption filter converter by creating a mock editor
 * and processing content with data-caption attributes.
 */

(function ($, Drupal) {
  // Mock the Drupal.CKEditor5PremiumFeatures object if it doesn't exist
  if (!Drupal.CKEditor5PremiumFeatures) {
    Drupal.CKEditor5PremiumFeatures = {};
  }

  // Include the caption filter converter
  document.write('<script src="../js/libraries/caption-filter-converter.js"></script>');

  // Test function
  async function testCaptionFilterConverter() {
    console.log('Testing caption filter converter...');

    // Sample content with data-caption attribute
    const testContent = '<p>This is a test paragraph.</p><img src="/test.jpg" alt="Test image" data-caption="This is a test caption" /><p>Another paragraph.</p>';

    console.log('Original content:');
    console.log(testContent);

    // Apply caption filter
    try {
      const processedContent = await Drupal.CKEditor5PremiumFeatures.captionFilterConverter.applyCaptionFilter(testContent);

      console.log('Processed content:');
      console.log(processedContent);

      // Check if the caption was applied
      if (processedContent.includes('class="caption"')) {
        console.log('Success: Caption filter was applied correctly.');
      } else {
        console.log('Error: Caption filter was not applied correctly.');
      }
    } catch (error) {
      console.error('Error:', error);
    }
  }

  // Run the test when the document is ready
  $(document).ready(function() {
    testCaptionFilterConverter();
  });

})(jQuery, Drupal);
