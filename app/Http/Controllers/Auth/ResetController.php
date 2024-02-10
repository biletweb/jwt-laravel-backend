<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetMail;

class ResetController extends Controller
{
    public function passwordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('email')) {
                return response()->json(['error' => ['message' => $errors->first('email')]], 400);
            }
        }

        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => ['message' => 'Email not found']], 404);
        }

        try {
            DB::beginTransaction();

            $tokenMail = Str::random(32);

            $userId = $user->id;

            $frontendDomain = env('FRONTEND_DOMAIN');
            $resetLink = $frontendDomain . '/auth/password/reset/' . $tokenMail . '/' . $userId;

            Mail::to($user->email)->send(new ResetMail($resetLink));

            $user->update(['verify_password' => $tokenMail]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => ['message' => 'Failed to send password reset email, try again later']], 400);
        }

        return response()->json(['message' => 'Password reset email sent, check your email'], 200);
    }

    public function passwordNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|string|min:6',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('password')) {
                return response()->json(['error' => ['message' => $errors->first('password')]], 400);
            }
        }

        $userId = $request->user;
        $tokenMail = $request->token;

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }

        if ($user->verify_password !== $tokenMail) {
            return response()->json(['error' => ['message' => 'Invalid token']], 400);
        }

        $newPassword = bcrypt($request->password);

        $user->update(['password' => $newPassword, 'verify_password' => null]);

        return response()->json(['message' => 'The password has been successfully changed'], 200);
    }
}
