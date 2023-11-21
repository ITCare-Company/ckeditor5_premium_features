<?php

namespace Drupal\ckeditor5_premium_features_ai_assistant\Entity;

use Drupal\ckeditor5_premium_features_ai_assistant\Ckeditor5AiCommandGroupInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the ckeditor 5 AI commands group entity type.
 *
 * @ConfigEntityType(
 *   id = "ckeditor5_ai_command_group",
 *   label = @Translation("CKEditor 5 AI Command group"),
 *   label_collection = @Translation("CKEditor 5 AI Commands groups"),
 *   label_singular = @Translation("ckeditor 5 ai commands group"),
 *   label_plural = @Translation("ckeditor 5 ai commands groups"),
 *   label_count = @PluralTranslation(
 *     singular = "@count ckeditor 5 ai commands group",
 *     plural = "@count ckeditor 5 ai commands groups",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\ckeditor5_premium_features_ai_assistant\Ckeditor5AiCommandGroupListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ckeditor5_premium_features_ai_assistant\Form\Ckeditor5AiCommandGroupForm",
 *       "edit" = "Drupal\ckeditor5_premium_features_ai_assistant\Form\Ckeditor5AiCommandGroupForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "ckeditor5_ai_command_group",
 *   admin_permission = "administer ckeditor5_ai_command_group",
 *   links = {
 *     "collection" = "/admin/structure/ckeditor5-ai-command-group",
 *     "add-form" = "/admin/structure/ckeditor5-ai-command-group/add",
 *     "edit-form" = "/admin/structure/ckeditor5-ai-command-group/{ckeditor5_ai_command_group}",
 *     "delete-form" = "/admin/structure/ckeditor5-ai-command-group/{ckeditor5_ai_command_group}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "commands",
 *     "textFormats",
 *   }
 * )
 */
class Ckeditor5AiCommandGroup extends ConfigEntityBase implements Ckeditor5AiCommandGroupInterface {

  /**
   * The ckeditor 5 AI Commands group ID.
   *
   * @var string
   */
  protected string $id;

  /**
   * The ckeditor 5 AI Commands group label.
   *
   * @var string
   */
  protected string $label;

  /**
   * The commands associated with the CommandGroup.
   *
   * @var array
   */
  protected ?array $commands;

  /**
   * Allowed text formats.
   *
   * @var array
   */
  protected ?array $textFormats;

  /**
   * Add command to the commands list.
   *
   * @param array $command
   *   Array with command values.
   *
   * @return Ckeditor5AiCommandGroup
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addCommand(array $command): static {
    $command['uuid'] = $this->uuidGenerator()->generate();
    $this->commands[] = $command;
    $this->set('commands', $this->commands)->save();
    return $this;
  }

  /**
   * Get command from commands list.
   *
   * @param string $uuid
   *   Command uuid.
   *
   * @return array
   */
  public function getCommandByUuid(string $uuid): array {
    $command = array_filter($this->commands, fn($command) => $command['uuid'] === $uuid);
    return reset($command);
  }

  /**
   * Remove command from commands list.
   *
   * @param string $uuid
   *   Command uuid.
   *
   * @return Ckeditor5AiCommandGroup
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function removeCommand(string $uuid):static {
    $commands = array_filter($this->commands, fn($command) => $command['uuid'] !== $uuid);
    $this->set('commands', $commands)->save();
    return $this;
  }

  /**
   * Update command values.
   *
   * @param array $command
   *   Array with command values.
   *
   * @return Ckeditor5AiCommandGroup
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateCommand(array $command): static {
    foreach ($this->commands as $key => $value) {
      if ($value['uuid'] === $command['uuid']) {
        $this->commands[$key] = $command;
        $this->save();
        return $this;
      }
    }
    return $this;
  }

  /**
   * Update weights of commands.
   *
   * @param array $weights
   *   Array with weights associated with commands.
   *
   * @return Ckeditor5AiCommandGroup
   */
  public function updateWeights(array $weights): static {
    foreach ($this->commands as $key => $command) {
      if (array_key_exists($command['uuid'], $weights)) {
        $this->commands[$key]['weight'] = $weights[$command['uuid']]['weight'];
      }
    }
    usort($this->commands, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });
    return $this;
  }

  /**
   * Returns an array of definitions.
   */
  public function getDefinition(): array {
    return [
      'id' => $this->id(),
      'label' => $this->label(),
      'commands' => $this->commands,
    ];
  }

}
