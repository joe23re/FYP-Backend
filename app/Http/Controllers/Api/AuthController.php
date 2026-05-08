<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

   public function register(Request $request)
{
    $fields = $request->validate([
        'username' => ['required', 'string', 'unique:users,username'],
        'email' => ['required', 'email', 'unique:users,email'],
        'phone_number' => ['required', 'string'],
        'password' => ['required', 'min:6'],
    ]);

    $phoneNumber = preg_replace('/[^0-9]/', '', $fields['phone_number']);

    if (str_starts_with($phoneNumber, '961')) {
        $phoneNumber = substr($phoneNumber, 3);
    }

    if (strlen($phoneNumber) !== 8) {
        return response()->json([
            'message' => 'Phone number must be 8 digits without +961.',
        ], 422);
    }

    $user = User::create([
        'username' => $fields['username'],
        'email' => $fields['email'],
        'phone_number' => $phoneNumber,
        'password' => Hash::make($fields['password']),
    ]);

    $token = $user->createToken('mobile-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ], 201);
}

    public function login(Request $request)
{
    $fields = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required'],
    ]);

    $user = User::where('username', $fields['username'])->first();

    if (! $user || ! Hash::check($fields['password'], $user->password)) {
        throw ValidationException::withMessages([
            'username' => ['Invalid credentials.'],
        ]);
    }

    $token = $user->createToken('mobile-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);
}


    public function updateProfile(Request $request)
{
    $user = $request->user();

    $fields = $request->validate([
        'username' => ['required', 'string', 'unique:users,username,' . $user->id],
        'phone_number' => ['required', 'string'],
        'password' => ['nullable', 'min:6'],
    ]);

    $user->username = $fields['username'];
    $user->phone_number = $fields['phone_number'];

    if (!empty($fields['password'])) {
        $user->password = Hash::make($fields['password']);
    }

    $user->save();

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user,
    ]);
}


    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}