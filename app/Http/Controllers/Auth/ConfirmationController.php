<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ConfirmationController extends Controller
{
    public function confirmEmail($token, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }

        if ($user->verify_email === $token) {

            $user->verify_email = null;
            
            $user->save();
            return response()->json(['message' => 'Email confirmed'], 200);
        } else {
            return response()->json(['error' => ['message' => 'Invalid email verification token']], 400);
        }
    }
}
