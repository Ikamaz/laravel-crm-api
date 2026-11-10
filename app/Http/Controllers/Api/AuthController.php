<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
        ]);

        $user->refresh();

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', strtolower($credentials['email']))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->tokenResponse($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userData($request->user()),
        ]);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        $token = $user->createToken('crm-api')->plainTextToken;

        return response()->json([
            'data' => $this->userData($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], $status);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
