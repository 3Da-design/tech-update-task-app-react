<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TaskController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(IndexTaskRequest $request, TaskService $tasks): JsonResponse
  {
    $collection = $tasks->listForDefaultUser($request->validated());

    return TaskResource::collection($collection)->response();
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreTaskRequest $request, TaskService $tasks): JsonResponse
  {
    $task = $tasks->createForDefaultUser($request->validated());

    return (new TaskResource($task))->response()->setStatusCode(201);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateTaskRequest $request, string $id, TaskService $tasks): JsonResponse
  {
    $taskId = $this->parseTaskId($id);
    $task = $tasks->updateForDefaultUser($taskId, $request->validated());

    return (new TaskResource($task))->response();
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id, TaskService $tasks): Response
  {
    $taskId = $this->parseTaskId($id);
    $tasks->deleteForDefaultUser($taskId);

    return response()->noContent();
  }

  private function parseTaskId(string $id): int
  {
    if (! ctype_digit($id)) {
      abort(404);
    }

    return (int) $id;
  }
}
