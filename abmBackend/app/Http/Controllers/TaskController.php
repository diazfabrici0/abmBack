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
     * Esta funcion lista todas las tareas, para el admin y para los usuarios 
     * asignados a las tareas.
     */

 public function index()
{
    $user = auth()->user(); // si usas Sanctum o auth

    if ($user->role === 'admin') {
        // Admin ve todas las tareas
        $tasks = Task::with('users')->get();
    } else {
        // Usuario estándar ve solo sus tareas
        $tasks = Task::with('users')
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();
    }

    return response()->json($tasks);
}



    /**
     * Crear una nueva tarea
     */
    public function store(Request $request)
    {
       $user = auth()->user();

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
     * Muestra una tarea específica
     */
    public function show(string $id)
    {
        $task = Task::with('users', 'confirmations')->findOrFail($id);
        $user = auth()->user();

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
        $user = auth()->user();

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
     * Eliminar una tarea existente
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Tarea eliminada']);
    }


    /**
     * Asignar usuarios a una tarea
     */
    public function assingUsers(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'user_ids' => 'required|array',
        ]);

        $task->users()->sync($validatedData['user_ids']);

        return response()->json($task->load('users'));
    }

    /**
     * eliminar usuarios asignados a una tarea
     */
    public function removeUsers(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'user_ids' => 'required|array',
        ]);

        $task->users()->detach($validatedData['user_ids']);

        return response()->json($task->load('users'));
    }

    /**
     * Confirmar la realización de una tarea por parte de un usuario
     */

   public function confirmTask($id)
{
    $task = Task::findOrFail($id);
    $user = auth()->user();

    // Verificar que el usuario esté asignado a la tarea
    if (!$task->users->contains($user->id)) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    // Registrar la confirmación del usuario
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

    // Si al menos uno confirma, la tarea pasa a "in_progress"
    if ($confirmedUsers > 0 && $task->status === 'pending') {
        $task->status = 'in_progress';
        $task->save();
    }

    // Si todos confirman, la tarea pasa a "completed"
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
