<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\Auth\ConfirmationController;
use App\Http\Controllers\Auth\ResetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'api', 'prefix' => 'users'], function ($router) {
    Route::get('/all', [UsersController::class, 'usersAll']);
    Route::get('/show/{user}', [UsersController::class, 'userShow']);
    Route::post('/create', [UsersController::class, 'userCreate'])->middleware('throttle:5,1');
    Route::get('/edit/{user}', [UsersController::class, 'userEdit']);
    Route::post('/update/{user}', [UsersController::class, 'userUpdate'])->middleware('throttle:5,1');
    Route::post('/delete/{user}', [UsersController::class, 'userDelete'])->middleware('throttle:5,1');
    Route::post('/avatar/update/{user}', [UsersController::class, 'userAvatarUpdate'])->middleware('throttle:5,1');
});

Route::get('/auth/email/confirm/{token}/{user}', [ConfirmationController::class, 'confirmEmail'])->name('confirmation');

Route::get('/auth/password/reset', [ResetController::class, 'passwordReset'])->name('reset');
Route::post('/auth/password/new', [ResetController::class, 'passwordNew']);