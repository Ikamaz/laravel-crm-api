<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCustomerNoteRequest;
use App\Http\Resources\CustomerNoteResource;
use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CustomerNoteController extends Controller
{
    public function index(Customer $customer): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', [CustomerNote::class, $customer]);

        $notes = $customer->notes()
            ->with('author')
            ->latest()
            ->paginate(15);

        return CustomerNoteResource::collection($notes);
    }

    public function store(StoreCustomerNoteRequest $request, Customer $customer): JsonResponse
    {
        $note = $customer->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        $note->load('author');

        return (new CustomerNoteResource($note))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Customer $customer, CustomerNote $note): Response
    {
        abort_unless($note->customer_id === $customer->id, 404);

        Gate::authorize('delete', $note);

        $note->delete();

        return response()->noContent();
    }
}
