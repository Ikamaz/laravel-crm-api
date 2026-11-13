<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        if (! $customer instanceof Customer) {
            return false;
        }

        Gate::authorize('create', [Task::class, $customer]);

        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_at' => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
