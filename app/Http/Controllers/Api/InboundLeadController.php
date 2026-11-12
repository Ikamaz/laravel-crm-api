<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InboundLeadResource;
use App\Models\InboundLead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class InboundLeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', InboundLead::class);

        $filters = $request->validate([
            'status' => ['sometimes', 'string', 'in:new,contacted,qualified,converted,closed'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $leads = InboundLead::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return InboundLeadResource::collection($leads);
    }

    public function show(InboundLead $inboundLead): InboundLeadResource
    {
        Gate::authorize('view', $inboundLead);

        return new InboundLeadResource($inboundLead);
    }

    public function update(Request $request, InboundLead $inboundLead): InboundLeadResource
    {
        Gate::authorize('update', $inboundLead);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,qualified,converted,closed'],
        ]);

        $inboundLead->update($validated);

        return new InboundLeadResource($inboundLead->refresh());
    }
}
