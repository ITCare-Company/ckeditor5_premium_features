<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Form;

use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "Export to Word" feature.
 */
class SettingsForm extends SharedBuildConfigFormBase {

  const NOTIFICATION_CONFIG = 'ckeditor5_premium_features_notifications.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return self::NOTIFICATION_CONFIG;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config($this->getFormId());


    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#description' => t('Subject of the email that will be sent to users.'),
      '#default_value' => $config->get('subject'),
    ];

    $messageConfig = $config->get('message');
    $form['message'] = [
      '#type' => 'text_format',
      '#title' => t('Message body'),
      '#description' => t('Body of the message sent to the users that collaborated on the updated node.'),
      '#default_value' => $messageConfig['value'] ?? '',
      '#format' => $messageConfig['test_format'] ?? 'full_html',
    ];

    $form['additional_info'] = [
      '#markup' => 'The Message field supports tokens that will be dynamically replaced by corresponding values.
      Currently supported tokens relate to Node and User entities, for example [node:title], [node:url], [user:name].<br/>
      For more, please check the below two sample lists:',
      'list' => [
        '#theme' => 'item_list',
        '#items' => [
          [
            '#markup' => '<a href="https://www.drupal.org/node/390482#token-node">Node tokens</a>',
          ],
          [
            '#markup' => '<a href="https://www.drupal.org/node/390482#drupal7tokenslist-token-user">User tokens</a>',
          ],
        ]
      ],
    ];

    return $form;
  }

}
