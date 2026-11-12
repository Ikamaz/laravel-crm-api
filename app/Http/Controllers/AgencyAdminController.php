<?php

namespace App\Http\Controllers;

use App\Models\InboundLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AgencyAdminController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('agency.admin.leads');
        }

        return view('agency.admin-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The supplied credentials could not be verified.',
            ])->onlyInput('email');
        }

        if (! $request->user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This account does not have administrator access.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('agency.admin.leads'));
    }

    public function leads(Request $request): View
    {
        Gate::authorize('viewAny', InboundLead::class);

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:new,contacted,qualified,converted,closed'],
        ]);

        $leads = InboundLead::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('agency.admin-leads', compact('leads'));
    }

    public function updateLeadStatus(Request $request, InboundLead $inboundLead): RedirectResponse
    {
        Gate::authorize('update', $inboundLead);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,qualified,converted,closed'],
        ]);

        $inboundLead->update($validated);

        return back()->with('status_updated', true);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agency.admin.login');
    }
}
