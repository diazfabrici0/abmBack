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

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:api');

    // Endpoints de tareas
    Route::get('/tasks', [TaskController::class, 'index'])
        ->middleware('auth:api');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])
    ->middleware('auth:api');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('auth:api');;
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->middleware('auth:api');;
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->middleware('auth:api');;
    Route::post('/tasks/{id}/confirm', [TaskController::class, 'confirmTask'])->middleware('auth:api');;


    // Endpoints de usuarios
    Route::get('/users', [UserController::class, 'index'])->middleware('auth:api');;
    Route::get('/users/{id}/tasks', [UserController::class, 'userTasks'])->middleware('auth:api');;
    Route::delete('/users/{id}', [UserController::class, 'destroyUser'])->middleware('auth:api');;
