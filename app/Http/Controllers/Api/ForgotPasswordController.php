<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    private function cleanPhoneNumber(?string $phoneNumber): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phoneNumber ?? '');

        if (str_starts_with($digits, '961')) {
            $digits = substr($digits, 3);
        }

        return substr($digits, -8);
    }

    private function findUserByPhone(string $phoneNumber)
    {
        $cleanInputPhone = $this->cleanPhoneNumber($phoneNumber);

        return User::all()->first(function ($user) use ($cleanInputPhone) {
            return $this->cleanPhoneNumber($user->phone_number) === $cleanInputPhone;
        });
    }

    public function sendCode(Request $request)
    {
        $fields = $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $phoneNumber = $this->cleanPhoneNumber($fields['phone_number']);

        if (strlen($phoneNumber) !== 8) {
            return response()->json([
                'message' => 'Phone number must be 8 digits.',
            ], 422);
        }

        $user = $this->findUserByPhone($phoneNumber);

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this phone number.',
            ], 404);
        }

        $code = (string) random_int(1000, 9999);

        PasswordResetOtp::where('phone_number', $phoneNumber)->delete();

        PasswordResetOtp::create([
            'phone_number' => $phoneNumber,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message' => 'Development verification code created successfully.',
            'phone_number' => $phoneNumber,
            'dev_code' => $code,
        ]);
    }

    public function verifyCode(Request $request)
    {
        $fields = $request->validate([
            'phone_number' => ['required', 'string'],
            'code' => ['required', 'string', 'size:4'],
        ]);

        $phoneNumber = $this->cleanPhoneNumber($fields['phone_number']);

        $otp = PasswordResetOtp::where('phone_number', $phoneNumber)
            ->where('code', $fields['code'])
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        if (now()->greaterThan($otp->expires_at)) {
            return response()->json([
                'message' => 'Verification code has expired.',
            ], 422);
        }

        $otp->verified_at = now();
        $otp->save();

        return response()->json([
            'message' => 'Code verified successfully.',
            'phone_number' => $phoneNumber,
        ]);
    }

    public function resetPassword(Request $request)
{
    $fields = $request->validate([
        'phone_number' => ['required', 'string'],
        'code' => ['required', 'string', 'size:4'],
        'password' => ['required', 'string', 'min:6'],
    ]);

    $phoneNumber = $this->cleanPhoneNumber($fields['phone_number']);

    $otp = PasswordResetOtp::where('phone_number', $phoneNumber)
        ->where('code', $fields['code'])
        ->whereNotNull('verified_at')
        ->latest()
        ->first();

    if (! $otp) {
        return response()->json([
            'message' => 'Please verify the code before resetting password.',
        ], 422);
    }

    if (now()->greaterThan($otp->expires_at)) {
        return response()->json([
            'message' => 'Verification code has expired.',
        ], 422);
    }

    $user = $this->findUserByPhone($phoneNumber);

    if (! $user) {
        return response()->json([
            'message' => 'No account found with this phone number.',
        ], 404);
    }

    $user->password = Hash::make($fields['password']);
    $user->save();

    $otp->delete();

    return response()->json([
        'message' => 'Password reset successfully.',
    ]);
}
}