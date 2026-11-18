<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListCustomersRequest;
use App\Http\Requests\Api\StoreCustomerRequest;
use App\Http\Requests\Api\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function index(ListCustomersRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $user = $request->user();
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        $customers = Customer::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('owner_id', $user->id))
            ->when(
                $filters['search'] ?? null,
                function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('company', 'like', "%{$search}%");
                    });
                },
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status),
            )
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        $customer = $request->user()->customers()->create($request->validated());

        return new CustomerResource($customer);
    }

    public function show(Customer $customer): CustomerResource
    {
        Gate::authorize('view', $customer);

        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer->update($request->validated());

        return new CustomerResource($customer->refresh());
    }

    public function destroy(Customer $customer)
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        return response()->noContent();
    }
}
