<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskService
{
  public function defaultUserId(): int
  {
    $email = config('task.default_user_email');

    $id = User::query()->where('email', $email)->value('id');

    if ($id === null) {
      throw new ModelNotFoundException(
        "Default task user not found for email [{$email}]. Run migrations and seeders.",
      );
    }

    return (int) $id;
  }

  /**
   * @param  array<string, mixed>  $data
   */
  public function createForDefaultUser(array $data): Task
  {
    unset($data['user_id']);

    return Task::create([
      ...$data,
      'user_id' => $this->defaultUserId(),
    ]);
  }
}
