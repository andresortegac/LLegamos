<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::where('email', mb_strtolower($validated['email']))->first();
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Las credenciales no son correctas.']]);
        }
        if ($user->status !== 'active') {
            return response()->json(['message' => 'La cuenta no está activa.'], 403);
        }
        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Debes verificar el correo antes de entrar.'], 403);
        }

        $user->tokens()->where('name', $validated['device_name'])->delete();
        $abilities = $user->role === 'conductor'
            ? ['profile:read', 'driver:location', 'driver:trips']
            : ['profile:read'];
        $token = $user->createToken($validated['device_name'], $abilities, now()->addDays(30));
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Sesión cerrada en este dispositivo.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'email_verified' => $user->hasVerifiedEmail(),
        ];
    }
}