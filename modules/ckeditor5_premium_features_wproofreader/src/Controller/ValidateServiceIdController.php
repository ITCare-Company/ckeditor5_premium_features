<?php

/*
 * Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_wproofreader\Controller;

use Drupal\ckeditor5_premium_features_wproofreader\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoint for validating WProofreader service id.
 */
final class ValidateServiceIdController extends ControllerBase {

  /**
   * Constructs the object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(private readonly ClientInterface $httpClient, ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('config.factory'),
    );
  }

  /**
   * Sends request to WebSpellCheckerApi to validate service id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Json response with information if the service id is valid.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function __invoke(Request $request): Response {
    $config = $this->configFactory->get(SettingsForm::WPROOFREADER_SETTINGS_ID);
    $customerId = ['customerid' => $config->get('service_id')];
    $requestBody = http_build_query($customerId) . '&format=json&app_type=proofreader_ck5&cmd=get_info';
    $options = [
      'body' => $requestBody,
      'headers' => [
        'Content-Type' => 'text/plain',
        'Origin' => $request->getSchemeAndHttpHost(),
      ],
    ];
    try {
      $response = $this->httpClient->request('POST', WebSpellCheckerApiProxyController::WEBSPELLCHECKER_ENDPOINT, $options);
      return new JsonResponse(['valid' => $response->getStatusCode() === Response::HTTP_OK], $response->getStatusCode());
    }
    catch (RequestException $exception) {
      $response = ['valid' => FALSE];
      if (str_contains($exception->getMessage(), 'Word usage quota')) {
        $response['usage_limit_error'] = TRUE;
      }
      return new JsonResponse($response, $exception->getCode());
    }
  }

}
