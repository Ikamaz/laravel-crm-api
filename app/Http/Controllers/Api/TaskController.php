<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListTasksRequest;
use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Customer;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(ListTasksRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $user = $request->user();

        $tasks = Task::query()
            ->with([
                'customer:id,owner_id,first_name,last_name,company',
                'creator:id,name',
                'assignee:id,name',
            ])
            ->when(! $user->isAdmin(), function ($query) use ($user): void {
                $query->where(function ($query) use ($user): void {
                    $query
                        ->whereHas('customer', fn ($customerQuery) => $customerQuery->where('owner_id', $user->id))
                        ->orWhere('assigned_to', $user->id);
                });
            })
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    'completed' => $query->whereNotNull('completed_at'),
                    'overdue' => $query
                        ->whereNull('completed_at')
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now()),
                    default => $query->whereNull('completed_at'),
                };
            })
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Customer $customer): JsonResponse
    {
        $attributes = $request->validated();
        $assigneeId = $attributes['assignee_id'] ?? null;
        unset($attributes['assignee_id']);

        $task = $customer->tasks()->create([
            ...$attributes,
            'created_by' => $request->user()->id,
            'assigned_to' => $assigneeId,
        ]);

        $task->load([
            'customer:id,first_name,last_name,company',
            'creator:id,name',
            'assignee:id,name',
        ]);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task->load([
            'customer:id,first_name,last_name,company',
            'creator:id,name',
            'assignee:id,name',
        ]));
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $attributes = $request->validated();

        if (array_key_exists('assignee_id', $attributes)) {
            $attributes['assigned_to'] = $attributes['assignee_id'];
            unset($attributes['assignee_id']);
        }

        $task->update($attributes);

        return new TaskResource($task->refresh()->load([
            'customer:id,first_name,last_name,company',
            'creator:id,name',
            'assignee:id,name',
        ]));
    }

    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
