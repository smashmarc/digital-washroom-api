<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BackupDestinationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevController;
use App\Http\Controllers\SeederController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationTemplateController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CriteriaCategoryController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\UserAssignmentController;
use App\Http\Controllers\DepartmentsController;
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
        Route::post('upload', [UsersController::class, 'upload'])->name('users.upload');
    });

    Route::prefix('rooms')->group(function () {
        Route::get('form-options', [RoomsController::class, 'getFormOptions']);
        Route::post('upload', [RoomsController::class, 'upload'])->name('rooms.upload');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportsController::class, 'index']);
        Route::get('export', [ReportsController::class, 'export']);
    });

    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('rooms/needs-attention', [DashboardController::class, 'needsAttention']);

    Route::post('force-change-password', [AuthController::class, 'forceChangePassword']);


    //====Backups=====//

    Route::prefix('backups')->group(function () {
        Route::get('/stats',                         [BackupController::class, 'stats']);
        Route::get('/schedules',                     [BackupController::class, 'schedules']);
        Route::post('/schedules',                    [BackupController::class, 'storeSchedule']);
        Route::patch('/schedules/{schedule}',        [BackupController::class, 'updateSchedule']);
        Route::delete('/schedules/{schedule}',       [BackupController::class, 'destroySchedule']);
        Route::post('/schedules/{schedule}/run',     [BackupController::class, 'runSchedule']);
        Route::post('/run',                          [BackupController::class, 'runManual']);
        Route::get('/logs',                          [BackupController::class, 'logs']);
        Route::delete('/logs',                       [BackupController::class, 'clearLogs']);
        Route::delete('/logs/{log}',                 [BackupController::class, 'destroyLog']);
        Route::get('/files',                         [BackupController::class, 'files']);
        Route::delete('/files/{filename}',           [BackupController::class, 'deleteFile']);
        Route::get('/files/{filename}/download',     [BackupController::class, 'downloadFile']);

        // Destinations
        Route::post('destinations/send-all',           [BackupDestinationController::class, 'sendToAll']);
        Route::get('destinations',                    [BackupDestinationController::class, 'index']);
        Route::post('destinations',                    [BackupDestinationController::class, 'store']);
        Route::patch('destinations/{destination}',      [BackupDestinationController::class, 'update']);
        Route::delete('destinations/{destination}',      [BackupDestinationController::class, 'destroy']);
        Route::post('destinations/{destination}/send', [BackupDestinationController::class, 'send']);
    });

    //=========//
// ── Criteria Categories ──────────────────────────────────────────────
    Route::prefix('criteria-categories')->group(function () {
        Route::get('form-options', [CriteriaCategoryController::class, 'getFormOptions']);
        Route::get('/',            [CriteriaCategoryController::class, 'index']);
        Route::post('/',           [CriteriaCategoryController::class, 'store']);
        Route::get('{criteriaCategory}',    [CriteriaCategoryController::class, 'show']);
        Route::put('{criteriaCategory}',    [CriteriaCategoryController::class, 'update']);
        Route::delete('{criteriaCategory}', [CriteriaCategoryController::class, 'destroy']);
    });

    // ── Criteria ────────────────────────────────────────────────────────
    Route::prefix('criteria')->group(function () {
        Route::get('form-options', [CriteriaController::class, 'getFormOptions']);
        Route::get('/',            [CriteriaController::class, 'index']);
        Route::post('/',           [CriteriaController::class, 'store']);
        Route::get('{criteria}',    [CriteriaController::class, 'show']);
        Route::put('{criteria}',    [CriteriaController::class, 'update']);
        Route::delete('{criteria}', [CriteriaController::class, 'destroy']);
    });

    // ── Evaluation Templates ─────────────────────────────────────────────
    Route::prefix('evaluation-templates')->group(function () {
        Route::get('form-options', [EvaluationTemplateController::class, 'getFormOptions']);
        Route::get('/',            [EvaluationTemplateController::class, 'index']);
        Route::post('/',           [EvaluationTemplateController::class, 'store']);
        Route::get('{evaluationTemplate}',    [EvaluationTemplateController::class, 'show']);
        Route::put('{evaluationTemplate}',    [EvaluationTemplateController::class, 'update']);
        Route::delete('{evaluationTemplate}', [EvaluationTemplateController::class, 'destroy']);

        // Attach / detach criteria
        Route::post('{evaluationTemplate}/criteria',              [EvaluationTemplateController::class, 'attachCriteria']);
        Route::delete('{evaluationTemplate}/criteria/{criteria}', [EvaluationTemplateController::class, 'detachCriteria']);
    });

    // ── User Assignments ─────────────────────────────────────────────────
    Route::prefix('user-assignments')->group(function () {
        Route::get('form-options', [UserAssignmentController::class, 'getFormOptions']);
        Route::get('/',            [UserAssignmentController::class, 'index']);
        Route::post('/',           [UserAssignmentController::class, 'store']);
        Route::get('{userAssignment}',    [UserAssignmentController::class, 'show']);
        Route::put('{userAssignment}',    [UserAssignmentController::class, 'update']);
        Route::delete('{userAssignment}', [UserAssignmentController::class, 'destroy']);
    });

    // ── Evaluations ──────────────────────────────────────────────────────
    Route::prefix('evaluations')->group(function () {
        Route::get('form-options', [EvaluationController::class, 'getFormOptions']);
        Route::get('/',            [EvaluationController::class, 'index']);
        Route::post('/',           [EvaluationController::class, 'store']);
        Route::get('{evaluation}',    [EvaluationController::class, 'show']);
        Route::put('{evaluation}',    [EvaluationController::class, 'update']);

        // Save / upsert answers (recalculates score automatically)
        Route::post('{evaluation}/answers', [EvaluationController::class, 'saveAnswers']);

        // Submit evaluation (locks and finalises result)
        Route::post('{evaluation}/submit', [EvaluationController::class, 'submit']);
    });

    // ── Standalone answer update (recalculates score automatically) ──────
    Route::put('answers/{answer}', [EvaluationController::class, 'updateAnswer']);


    //======//
    Route::prefix('dev/seeders')->group(function () {
        Route::get('/',     [SeederController::class, 'index']);
        Route::post('/run', [SeederController::class, 'run']);
    });

    Route::prefix('system-log')->group(function () {
        Route::get('/', [SystemLogController::class, 'index']);
        Route::delete('/', [SystemLogController::class, 'clear']);
    });

    /**
     * Route Resource should be in Bottom to avoid overriding
     */
    Route::apiResource('departments', DepartmentsController::class);
    Route::apiResource('locations', LocationsController::class);
    //override for patch method spoofing
    Route::post('/locations/{location}', [LocationsController::class, 'update']);
    Route::apiResource('rooms', RoomsController::class);
    Route::get('logs/export', [LogsController::class, 'export']);
    Route::apiResource('logs', LogsController::class);
    Route::apiResource('users', UsersController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('roles', RolesController::class);
});



// Route::controller(LogsController::class)->group(function () {
//     Route::get('logs', 'index');
//     Route::post('logs', 'store');
// });