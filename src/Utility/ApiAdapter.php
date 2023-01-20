<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the CKEditor API connection.
 */
class ApiAdapter {

  /**
   * Creates the Track Changes plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(protected SettingsConfigHandlerInterface $settingsConfigHandler, protected ClientInterface $http_client) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('ckeditor5_premium_features.config_handler.settings')
    );
  }

  /**
   * Call flush all collaborative sessions endpoint.
   */
  public function flushAllCollaborativeSessions(): void {
    $this->sendRequest('DELETE', 'collaborations');
  }

  /**
   * Call to get document details of collaborative session.
   *
   * @param string $documentId
   *   The document id.
   *
   * @return array
   *   Response of the request.
   */
  public function getCollaborativeSessionDetails(string $documentId): array {
    return $this->sendRequest('GET', 'collaborations/' . $documentId . '/details');
  }

  /**
   * Deletes do document on the collaboration server.
   *
   * @param string $documentId
   *   Document ID.
   */
  public function deleteDocument(string $documentId): bool {
    $result = $this->sendRequest('DELETE', 'collaborations/' . $documentId . '?force=true&wait=true');

    return empty($result);
  }

  /**
   * Check the library version used in last session.
   *
   * @param string $documentId
   *   The document id.
   *
   * @return string|null
   *   Library version
   */
  public function getLibraryVersion(string $documentId): ?string {
    $details = $this->getCollaborativeSessionDetails($documentId);
    if (!empty($details['current_session'])) {
      return $details['current_session']['bundle_version'];
    }
    return NULL;
  }

  /**
   * Validate session library version with used in Drupal.
   *
   * @param string $documentId
   *   The document id.
   */
  public function validateLibraryVersion(string $documentId): void {
    $sessionVersion = $this->getLibraryVersion($documentId);
    $libraryVersion = $this->settingsConfigHandler->getDllVersion();
    if (is_null($sessionVersion) || $sessionVersion === $libraryVersion) {
      return;
    }
    else {
      $this->flushAllCollaborativeSessions();
    }
  }

  /**
   * Base URL of API.
   *
   * @return string
   *   Base URL.
   */
  private function getBaseUrl(): String {
    return $this->settingsConfigHandler->getApiUrl();
  }

  /**
   * Generate signature for request.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Request url.
   * @param int $timestamp
   *   Timestamp.
   * @param array $body
   *   Request body.
   *
   * @return string
   *   Generated signature.
   */
  private function generateSignature(string $method, string $url, int $timestamp, array $body): String {
    $parsedUrl = parse_url($url);
    $uri = $parsedUrl['path'] ?? '';

    if (isset($parsedUrl['query'])) {
      $uri .= '?' . $parsedUrl['query'];
    }

    $data = $method . $uri . $timestamp;

    if ($body) {
      $data .= JSON::encode($body);
    }
    $key = $this->settingsConfigHandler->getApiKey();
    return hash_hmac('sha256', $data, $key);
  }

  /**
   * Send request to API.
   *
   * @param string $method
   *   Request method.
   * @param string $path
   *   Request path.
   *
   * @return array
   *   Result of sent request.
   */
  private function sendRequest(string $method, string $path): array {
    $url = $this->getBaseUrl() . $path;
    $timestamp = hrtime(TRUE);

    $signature = $this->generateSignature($method, $url, $timestamp, []);

    $options = [
      'headers' => [
        'X-CS-Signature' => $signature,
        'X-CS-Timestamp' => $timestamp,
      ],
    ];

    try {
      $request = $this->http_client->request($method, $url, $options);
    }
    catch (GuzzleException $e) {
      // Log the error.
      watchdog_exception('ckeditor5_premium_features', $e);
      return [];
    }

    $response = $request->getBody()->getContents();

    return empty($response) ? [] : (array) Json::decode($response);
  }

}
