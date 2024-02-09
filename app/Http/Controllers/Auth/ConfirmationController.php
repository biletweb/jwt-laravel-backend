<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ConfirmationController extends Controller
{
    public function emailConfirm(Request $request)
    {
        $tokenMail = $request->token;
        $userId = $request->user;

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }

        if ($user->verify_email !== $tokenMail) {
            return response()->json(['error' => ['message' => 'Invalid token']], 400);
        }

        $user->update(['verify_email' => null]);

        return response()->json(['message' => 'Email confirmed'], 200);
    }
}
