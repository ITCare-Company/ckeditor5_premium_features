<?php

namespace Drupal\ckeditor5_premium_features_ai_assistant\Form;

use Drupal\ckeditor5_premium_features_ai_assistant\Entity\Ckeditor5AiCommandGroup;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * CKEditor 5 AI Command add form.
 */
class Ckeditor5AiCommandAddForm extends FormBase {

  /**
   * Command group entity.
   *
   * @var \Drupal\ckeditor5_premium_features_ai_assistant\Entity\Ckeditor5AiCommandGroup|null
   */
  protected ?Ckeditor5AiCommandGroup $commandGroup;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Ckeditor5AiCommandGroup $ckeditor5_ai_command_group = NULL, string $uuid = NULL): array {
    $this->commandGroup = $ckeditor5_ai_command_group;
    $command = [];
    if ($uuid) {
      $command = $this->commandGroup->getCommandByUuid($uuid);
    }
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $command['label'] ?? '',
      '#description' => $this->t('Label for the ckeditor 5 ai command.'),
      '#required' => TRUE,
    ];

    $form['command_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Command id'),
      '#default_value' => $command['command_id'] ?? '',
      '#required' => TRUE,
      '#description' => $this->t('Id for the ckeditor 5 ai command.'),
    ];

    $form['prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Prompt'),
      '#required' => TRUE,
      '#default_value' => $command['prompt'] ?? '',
      '#description' => $this->t('Description of the ckeditor 5 ai command.'),
    ];

    $form['weight'] = [
      '#type' => 'weight',
      '#title_display' => 'invisible',
      "#disabled" => TRUE,
      "#access" => FALSE,
      '#default_value' => $command['weight'] ?? 0,
    ];

    $form['uuid'] = [
      '#type' => 'string',
      '#title_display' => 'invisible',
      "#disabled" => TRUE,
      "#access" => FALSE,
      '#default_value' => $command['uuid'] ?? '',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
    ];

    $form['actions']['submit']['#value'] = $this->t('Add Command');

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    $values['weight'] = 0;
    $values['command_id'] = strip_tags(str_replace(' ', '', $values['command_id']));
    $this->commandGroup->addCommand($values);
    $form_state->setRedirectUrl($this->commandGroup->toUrl('edit-form'));
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'ckeditor5_ai_command_add_form';
  }

}
