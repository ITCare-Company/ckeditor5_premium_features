<?php

namespace Drupal\ckeditor5_premium_features_ai_assistant\Form;

use Drupal\ckeditor5_premium_features_ai_assistant\Entity\Ckeditor5AiCommandGroup;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * CKEditor 5 AI Command delete form.
 */
class Ckeditor5AiCommandDeleteForm extends ConfirmFormBase {

  /**
   * Command group entity.
   *
   * @var \Drupal\ckeditor5_premium_features_ai_assistant\Entity\Ckeditor5AiCommandGroup|null
   */
  protected ?Ckeditor5AiCommandGroup $commandGroup;

  /**
   * @var mixed|null
   */
  protected mixed $commandUuid;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the command from the group?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->commandGroup->toUrl('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ConfigEntityInterface $ckeditor5_ai_command_group = NULL, $uuid = NULL) {
    $this->commandGroup = $ckeditor5_ai_command_group;
    $this->commandUuid = $uuid;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->commandGroup->removeCommand($this->commandUuid);
    $this->messenger()->addStatus($this->t('The command has been deleted.'));
    $form_state->setRedirectUrl($this->commandGroup->toUrl('edit-form'));
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'ckeditor5_ai_command_delete_form';
  }

}
