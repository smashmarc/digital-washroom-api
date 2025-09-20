<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('rooms/search', [RoomsController::class, 'search']);


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/me', [AuthController::class, 'me']);

Route::post('/dev/create-superadmin', [DevController::class, 'createSuperAdmin']);


Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
    Route::prefix('roles')->group(function () {
        Route::post('{role}/attache-permissions', [RolesController::class, 'attachePermissions']);
    });



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