<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Generator;

use Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Firebase\JWT\JWT;

/**
 * Provides the JWT Token generator service.
 */
class TokenGenerator implements TokenGeneratorInterface {

  public const ALGORITHM = 'HS512';

  /**
   * Constructs the token generator instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param \Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings config handler.
   *
   * @note The account will be used later in collaboration features.
   */
  public function __construct(
    protected AccountProxyInterface $account,
    protected SettingsConfigHandlerInterface $settingsConfigHandler,
  ) {
  }

  /**
   * Generates the JWT token.
   *
   * @return string
   *   The token.
   */
  public function generate(): string {
    $payload = [
      'aud' => $this->settingsConfigHandler->getEnvironmentId(),
      'iat' => time(),
      'sub' => $this->account->id(),
      'user' => [
        'email' => $this->account->getEmail(),
        'name' => $this->account->getAccountName(),
      ],
      'auth' => [
        'collaboration' => [
          '*' => [
            'role' => 'writer',
          ],
        ],
      ],
    ];
    if (empty($payload['user']['email'])) {
      unset($payload['user']['email']);
    }

    return JWT::encode($payload, $this->settingsConfigHandler->getAccessKey(), static::ALGORITHM);
  }

}
