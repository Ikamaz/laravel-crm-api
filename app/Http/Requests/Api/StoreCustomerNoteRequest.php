<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreCustomerNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        if (! $customer instanceof Customer) {
            return false;
        }

        Gate::authorize('create', [CustomerNote::class, $customer]);

        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }
}
