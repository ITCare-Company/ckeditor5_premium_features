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
   * @return void
   */
  public function flushAllCollaborativeSessions():void {
    $this->sendRequest('DELETE', 'collaborations');
  }

  /**
   * Call to get document details of collaborative session.
   * @param $documentId
   *
   * @return array
   */
  public function getCollaborativeSessionDetails($documentId):array {
    return $this->sendRequest('GET', 'collaborations/' . $documentId . '/details');
  }

  /**
   * Check the library version used in last session.
   *
   * @param $documentId
   *
   * @return String|NULL
   *   Library version
   */
  public function getLibraryVersion($documentId):?String {
    $details = $this->getCollaborativeSessionDetails($documentId);
    if (!empty($details['current_session'])) {
      return $details['current_session']['bundle_version'];
    }
    return NULL;
  }

  /**
   * Validate session library version with used in Drupal.
   *
   * @param $documentId
   *
   * @return void
   */
  public function validateLibraryVersion($documentId):void {
    $sessionVersion = $this->getLibraryVersion($documentId);
    $libraryVersion = $this->settingsConfigHandler->getDllVersion();
    if ($sessionVersion === $libraryVersion) {
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
  private function getBaseUrl():String {
    return 'https://' . $this->settingsConfigHandler->getOrganizationId() . '.cke-cs.com/api/v4/' . $this->settingsConfigHandler->getEnvironmentId() . '/';
  }

  /**
   * @param $method
   *   Request method.
   * @param $url
   *   Request url.
   * @param $timestamp
   *   Timestamp.
   * @param $body
   *   Request body.
   *
   * @return string
   *   Generated signature.
   */
  private function generateSignature($method, $url, $timestamp, $body):String {
    $parsedUrl = parse_url($url);
    $uri = $parsedUrl['path'] ?? '';

    if (isset($parsedUrl['query'])) {
      $uri .= '?' . $parsedUrl['query'];
    }

    $data = $method . $uri . $timestamp;

    if ($body) {
      $data .= JSON::encode($body);
    }
    $key = $this->settingsConfigHandler->getAccessKey();
    return hash_hmac('sha256', $data, $key);
  }

  /**
   * Send request to API.
   *
   * @param String $method
   *   Request method.
   * @param String $path
   *   Request path.
   * @param array $data
   *   Array with data to send request.
   *
   * @return array
   *   Result of sent request.
   */
  private function sendRequest($method, $path, $data = [] ):array {
    $url = $this->getBaseUrl() . $path;
    $timestamp = time();

    $signature = $this->generateSignature($method, $url, $timestamp, '');

    $options = [
      'headers' => [
        'X-CS-Signature' => $signature,
        'X-CS-Timestamp' => $timestamp,
      ]
    ];

    try {
      $request = $this->http_client->request($method, $url, $options);
    }
    catch (GuzzleException $e){
      // Log the error.
      watchdog_exception('ckeditor5_premium_features', $e);
      return [];
    }

    $response = $request->getBody()->getContents();

    return empty($response) ? [] : (array) Json::decode($data);
  }

}
