<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function userResponse(User $user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'phone_number' => $user->phone_number,
            'email' => $user->email,
            'password_hash' => $user->password,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    private function strongPasswordRules()
    {
        return [
            'required',
            'string',
            'min:8',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
            'regex:/[@$!%*#?&]/',
        ];
    }

    private function nullableStrongPasswordRules()
    {
        return [
            'nullable',
            'string',
            'min:8',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
            'regex:/[@$!%*#?&]/',
        ];
    }

    public function register(Request $request)
{
    $request->merge([
        'email' => $request->email ? trim(strtolower($request->email)) : null,
    ]);

    $fields = $request->validate([
        'username' => ['required', 'string', 'unique:users,username'],
        'phone_number' => ['required', 'string', 'unique:users,phone_number'],
        'email' => ['nullable', 'email', 'unique:users,email'],
        'password' => $this->strongPasswordRules(),
    ], [
        'password.min' => 'Password must be at least 8 characters.',
        'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
    ]);

    $user = User::create([
        'username' => $fields['username'],
        'phone_number' => $fields['phone_number'],
        'email' => $fields['email'] ?? null,
        'password' => Hash::make($fields['password']),
    ]);

    $token = $user->createToken('mobile-token')->plainTextToken;

    return response()->json([
        'user' => $this->userResponse($user),
        'token' => $token,
    ], 201);
}

    public function login(Request $request)
    {
        $fields = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $fields['username'])->first();

        if (! $user || ! Hash::check($fields['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'user' => $this->userResponse($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userResponse($request->user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $fields = $request->validate([
            'username' => [
                'required',
                'string',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'phone_number' => [
                'required',
                'string',
                Rule::unique('users', 'phone_number')->ignore($user->id),
            ],
            'password' => $this->nullableStrongPasswordRules(),
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $user->username = $fields['username'];
        $user->phone_number = $fields['phone_number'];

        if (!empty($fields['password'])) {
            $user->password = Hash::make($fields['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $this->userResponse($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}