<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('rooms/search', [RoomsController::class, 'search']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/me', [AuthController::class, 'me']);
Route::post('/dev/create-superadmin', [DevController::class, 'createSuperAdmin']);
Route::get('rooms/qr-view/{room}', [RoomsController::class, 'qrView']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::prefix('roles')->group(function () {
        Route::get('form-options', [RolesController::class, 'getFormOptions']);
        Route::post('{role}/attache-permissions', [RolesController::class, 'attachePermissions']);
    });

    Route::prefix('users')->group(function () {
        Route::get('form-options', [UsersController::class, 'getFormOptions']);
    });

    Route::prefix('rooms')->group(function () {
        Route::get('form-options', [RoomsController::class, 'getFormOptions']);
        Route::post('upload', [RoomsController::class, 'upload'])->name('rooms.upload');
    });

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::post('force-change-password', [AuthController::class, 'forceChangePassword']);

    Route::post('/users/upload', [UsersController::class, 'upload'])->name('users.upload');
    /**
     * Route Resource should be in Bottom to avoid overriding
     */
    Route::apiResource('locations', LocationsController::class);
    Route::apiResource('rooms', RoomsController::class);
    Route::apiResource('logs', LogsController::class);
    Route::apiResource('users', UsersController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('roles', RolesController::class);
});



// Route::controller(LogsController::class)->group(function () {
//     Route::get('logs', 'index');
//     Route::post('logs', 'store');
// });