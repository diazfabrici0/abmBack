<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// Endpoints públicos
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Refresh token (requiere JWT válido)
Route::post('/refresh', [AuthController::class, 'refresh'])
    ->middleware('auth:api');

// Rutas protegidas con JWT
Route::middleware('auth:api')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoints de tareas
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{id}/confirm', [TaskController::class, 'confirmTask']);


    // Endpoints de usuarios
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}/tasks', [UserController::class, 'userTasks']);
    Route::delete('/users/{id}', [UserController::class, 'destroyUser']);
});
