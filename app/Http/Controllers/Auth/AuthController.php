<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Mail\ConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|string|min:6',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('name')) {
                return response()->json(['error' => ['message' => $errors->first('name')]], 400);
            }

            if ($errors->has('email')) {
                return response()->json(['error' => ['message' => $errors->first('email')]], 400);
            }

            if ($errors->has('password')) {
                return response()->json(['error' => ['message' => $errors->first('password')]], 400);
            }
        }

        try {
            DB::beginTransaction();

            $user = User::create(array_merge($validator->validated(), ['password' => bcrypt($request->password)]));

            $tokenMail = Str::random(32);

            $userId = $user->id;

            $frontendDomain = env('FRONTEND_DOMAIN');
            $confirmationLink = $frontendDomain . '/auth/email/confirm/' . $tokenMail . '/' . $userId;

            Mail::to($user->email)->send(new ConfirmationMail($confirmationLink));

            $user->update(['verify_email' => $tokenMail]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => ['message' => 'Failed to send confirmation email, try again later']], 400);
        }

        return response()->json(['message' => 'User successfully registered'], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('email')) {
                return response()->json(['error' => ['message' => $errors->first('email')]], 400);
            }

            if ($errors->has('password')) {
                return response()->json(['error' => ['message' => $errors->first('password')]], 400);
            }
        }

        $user = User::where('email', $request->email)->first();

        if ($user->verify_email) {
            return response()->json(['error' => ['message' => 'You have not confirmed email']], 400);
        }

        $credentials = request(['email', 'password']);

        if (!($token = auth()->attempt($credentials))) {
            return response()->json(['error' => ['message' => 'Unauthorized']], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1,
        ]);
    }
}
