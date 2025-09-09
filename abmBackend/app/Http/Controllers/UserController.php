<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return User::select('id', 'name', 'email','role')->get();
    }

    // Ver tareas de un usuario específico
    public function userTasks($id)
    {
        $authUser = auth()->user();
        if ($authUser->role !== 'admin' && $authUser->id != $id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);
        return $user->tasks()->with('users','confirmations')->get();
    }
public function destroyUser($id)
{
    $user = auth()->user();

    if ($user->role !== 'admin') {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    $userToDelete = User::findOrFail($id);

    // Obtener todas las tareas donde estaba asignado
    $tasks = $userToDelete->tasks()->with('confirmations', 'users')->get();

    foreach ($tasks as $task) {
        // Marcar confirmación de este usuario como completada
        \App\Models\Confirmation::updateOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $userToDelete->id,
            ],
            ['confirmed' => true]
        );

        // Recalcular si la tarea queda completada
        $totalUsers = $task->users()->count();
        $confirmedUsers = $task->confirmations()->where('confirmed', true)->count();

        if ($totalUsers > 0 && $confirmedUsers === $totalUsers) {
            $task->status = 'completed';
            $task->save();
        }
    }

    // Finalmente eliminar el usuario
    $userToDelete->delete();

    return response()->json([
        'message' => 'Usuario eliminado y tareas actualizadas automáticamente'
    ]);
}


}
