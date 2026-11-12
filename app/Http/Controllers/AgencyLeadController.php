<?php

namespace App\\Http\\Controllers;

use App\\Models\\InboundLead;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\ValidationException;

class AgencyLeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Honeypot field for simple bot filtering. Never persist its value.
        if ($request->filled('website')) {
            return back()->with('lead_success', true);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'company' => ['nullable', 'string', 'max:160'],
            'service' => ['required', 'in:website,automation,bundle,other'],
            'message' => ['nullable', 'string', 'max:3000'],
            'locale' => ['required', 'in:ka,en'],
            'consent' => ['accepted'],
        ]);

        InboundLead::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'service' => $validated['service'],
            'message' => $validated['message'] ?? null,
            'locale' => $validated['locale'],
            'status' => 'new',
            'consented_at' => now(),
        ]);

        return back()->with('lead_success', true);
    }
}
