<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Confirmation;

class TaskController extends Controller
{
    /**
     * lista las tareas para los usuarios que estan asignados a ella
     * (el admin ve todas las tareas)
     */

     public function index()
    {
        $user = auth('api')->user(); 

        if ($user->role === 'admin') {
            $tasks = Task::with('users')->get();
        } else {
            $tasks = Task::with('users')
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();
        }

        return response()->json($tasks);
    }



    /**
     * crear una nueva tarea
     */
    public function store(Request $request)
    {
       $user = auth('api')->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|in:pending,in_progress,completed',
            'priority' => 'required|string|in:low,medium,high',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $task = Task::create($validatedData);

        if (isset($validatedData['user_ids'])) {
            $task->users()->sync($validatedData['user_ids']);
        }

        return response()->json($task, 201);
    }

    /**
     * muestra el detalle de una tarea específica
     */
    public function show(string $id)
    {
        $task = Task::with('users', 'confirmations')->findOrFail($id);
        $user = auth('api')->user();

        if ($user->role !== 'admin' && !$task->users->contains($user)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($task);
    }

    /**
     * editar una tarea existente
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);
        $user = auth('api')->user();

        if ($user->role !== 'admin' && !$task->users->contains($user)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed',
            'priority' => 'sometimes|required|string|in:low,medium,high',
            'user_ids' => 'sometimes|array',
            'user_ids.*' => 'exists:users,id',
        ]);


        $task->update($validatedData);

        if (isset($validatedData['user_ids'])) {
            $task->users()->sync($validatedData['user_ids']);
        }


        return response()->json($task->load('users', 'confirmations'));
    }

    /**
     * eliminar una tarea existente
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $user = auth('api')->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Tarea eliminada']);
    }

    /**
     * se guarda la confirmacion de una tarea por parte de un usuario
     */
    public function confirmTask($id)
    {
        $task = Task::findOrFail($id);
        $user = auth('api')->user();

        // verificar que el usuario esté asignado a la tarea
        if (!$task->users->contains($user->id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // registrar la confirmación del usuario
        $confirmation = Confirmation::updateOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $user->id,
            ],
            [
                'confirmed' => true,
            ]
        );

        $totalUsers = $task->users()->count();
        $confirmedUsers = $task->confirmations()->where('confirmed', true)->count();

        // si al menos uno confirma, la cambia de estado a "in_progress"
        if ($confirmedUsers > 0 && $task->status === 'pending') {
            $task->status = 'in_progress';
            $task->save();
        }

        // si todos confirman, la tarea cambia de estado a "completed"
        if ($confirmedUsers === $totalUsers) {
            $task->status = 'completed';
            $task->save();
        }

        return response()->json([
            'message' => $confirmedUsers === $totalUsers
                ? 'Todos confirmaron, tarea completada ✅'
                : 'Confirmación registrada',
            'task' => $task->load('users', 'confirmations'),
            'confirmation' => $confirmation,
        ]);
    }   

}
