<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * funcion para listar a todos los usuarios (solo admin)
     */
    public function index()
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return User::select('id', 'name', 'email','role')->get();
    }

   /**
    * ver las tareas de un usuario en especifico
    */
    public function userTasks($id)
    {
        $authUser = auth('api')->user();
        if ($authUser->role !== 'admin' && $authUser->id != $id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);
        return $user->tasks()->with('users','confirmations')->get();
    }

    /**
     * borrado logico de un usuario existente
     */
    public function destroyUser($id)
    {
        $user = auth('api')->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $userToDelete = User::findOrFail($id);

        $tasks = $userToDelete->tasks()->with('confirmations', 'users')->get();

        foreach ($tasks as $task) {
            // se marca la confirmación de este usuario como completada 
            //(esto para que si los demas usuarios asignados, completan y solo falta el eliminado, pase a completed automaticamente)
            \App\Models\Confirmation::updateOrCreate(
                [
                    'task_id' => $task->id,
                    'user_id' => $userToDelete->id,
                ],
                ['confirmed' => true]
            );

            // verificar si la tarea queda completada
            $totalUsers = $task->users()->count();
            $confirmedUsers = $task->confirmations()->where('confirmed', true)->count();

            if ($totalUsers > 0 && $confirmedUsers === $totalUsers) {
                $task->status = 'completed';
                $task->save();
            }
        }
        $userToDelete->delete();

        return response()->json([
            'message' => 'Usuario eliminado y tareas actualizadas automáticamente'
        ]);
    }


}
