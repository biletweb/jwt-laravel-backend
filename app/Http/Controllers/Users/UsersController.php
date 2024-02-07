<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function usersAll(Request $request)
    {
        $sort = $request->sort;
        $search = $request->search;

        if($request->input('search'))
        {
            return User::where('name', 'LIKE', "%{$search}%")->orderBy('id', $sort)->paginate(5);
        }

        return User::orderBy('id', $sort)->paginate(5);
    }

    public function userShow($user)
    {
        $user = User::find($user);

        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }
        
        return $user;
    }

    public function userEdit($user)
    {
        $user = User::find($user);

        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }
       
        return $user;
    }

    public function userUpdate(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id
        ]);

        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('name')) {
                return response()->json(['error' => ['message' => $errors->first('name')]], 400);
            }

            if ($errors->has('email')) {
                return response()->json(['error' => ['message' => $errors->first('email')]], 400);
            }
        }

        $user->update($validator->validated());
       
        
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function userCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|string|min:6'
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
        
        $user = User::create(array_merge(
            $validator->validated(), ['password' => bcrypt($request->password)]
        ));

        return response()->json(['message' => 'User successfully created'], 200);
    }

    public function deleteUser(User $user)
    {
        if (!$user) {
            return response()->json(['error' => ['message' => 'User not found']], 404);
        }
        
        if ($user->id !== auth()->user()->id) {
            $user->delete();

            return response()->json(['message' => 'User deleted successfully'], 200);
        } else {
            return response()->json(['error' => ['message' => 'Are you trying to remove yourself?']], 404);
        }
    }

    public function userAvatarUpdate(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'image|mimes:jpeg,jpg,png|max:1024'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('avatar')) {
                return response()->json(['error' => ['message' => $errors->first('avatar')]], 400);
            }
        }

        if ($user->avatar) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $data = $validator->validated();
        $avatarPath = \Storage::disk('public')->putFile('img/profile/avatar', $request->file('avatar'));
        $data['avatar'] = $avatarPath;
        $user->update($data);

        return response()->json(['message' => 'User avatar updated successfully'], 200);
    } 
}
