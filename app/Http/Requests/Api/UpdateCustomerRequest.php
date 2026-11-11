<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        if (! $customer instanceof Customer) {
            return false;
        }

        Gate::authorize('update', $customer);

        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'min:1', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'min:1', 'max:100'],
            'email' => ['sometimes', 'nullable', 'email', 'max:254'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'company' => ['sometimes', 'nullable', 'string', 'max:150'],
            'position' => ['sometimes', 'nullable', 'string', 'max:120'],
            'source' => ['sometimes', 'nullable', 'string', 'max:80'],
            'status' => ['sometimes', 'required', Rule::in(Customer::STATUSES)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $editableFields = array_keys($this->rules());

                if (array_intersect($editableFields, array_keys($this->all())) === []) {
                    $validator->errors()->add('data', 'Provide at least one customer field to update.');
                }
            },
        ];
    }
}
