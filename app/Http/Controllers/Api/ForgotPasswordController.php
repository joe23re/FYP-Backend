<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    private function cleanEmail(?string $email): string
    {
        return strtolower(trim($email ?? ''));
    }

    public function sendCode(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $this->cleanEmail($fields['email']);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this email address.',
            ], 404);
        }

        $code = (string) random_int(1000, 9999);

        PasswordResetOtp::where('email', $email)->delete();

        PasswordResetOtp::create([
            'email' => $email,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::send('emails.forgot-password-code', [
            'code' => $code,
        ], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Your VAGDIAG verification code');
        });

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:4'],
        ]);

        $email = $this->cleanEmail($fields['email']);

        $otp = PasswordResetOtp::where('email', $email)
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        if (now()->greaterThan($otp->expires_at)) {
            $otp->delete();

            return response()->json([
                'message' => 'Verification code has expired.',
            ], 422);
        }

        if (! Hash::check($fields['code'], $otp->code)) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $otp->verified_at = now();
        $otp->save();

        return response()->json([
            'message' => 'Code verified successfully.',
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $fields = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:4'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $email = $this->cleanEmail($fields['email']);

        $otp = PasswordResetOtp::where('email', $email)
            ->whereNotNull('verified_at')
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'Please verify the code before resetting password.',
            ], 422);
        }

        if (now()->greaterThan($otp->expires_at)) {
            $otp->delete();

            return response()->json([
                'message' => 'Verification code has expired.',
            ], 422);
        }

        if (! Hash::check($fields['code'], $otp->code)) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No account found with this email address.',
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