<?php

namespace App\Http\Controllers;

use App\Models\Compromops;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FrappeController extends Controller
{
    public function view()
    {
        return view('frappe.frappe');
    }

    public function getTasks()
    {
        try {
            $tasks = Compromops::whereNotNull('finicio')
                ->whereNotNull('ftermino')
                ->get();

            if ($tasks->isEmpty()) {
                return response()->json([]);
            }

            $formattedTasks = $tasks->map(function ($task) {
                $startDate = Carbon::parse($task->finicio);
                $endDate = Carbon::parse($task->ftermino);
                $isActive = $task->isActive(); // Usar el método del modelo
                
                return [
                    'id' => (string) $task->id,
                    'name' => 'OP ' . $task->op . ($isActive ? '' : ' (Inactiva)'),
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'progress' => $task->fterminoreal ? 100 : 0,
                    'dependencies' => '',
                    'readonly' => !$isActive,
                    'custom_class' => !$isActive ? 'task-inactive' : 'task-active'
                ];
            });

            return response()->json($formattedTasks);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar fechas de una tarea específica
     */
    public function updateDates(Request $request, $id)
    {
        try {
            // Buscar la tarea
            $task = Compromops::find($id);
            
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarea no encontrada'
                ], 404);
            }
            
            // Verificar si está activa usando el método del modelo
            if (!$task->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede modificar una tarea inactiva. La tarea está deshabilitada.'
                ], 403); // Cambiar a 403 Forbidden
            }
            
            // Validar las fechas
            $request->validate([
                'start' => 'required|date',
                'end' => 'required|date|after_or_equal:start',
                'comment' => 'nullable|string|max:500'
            ]);
            
            // Guardar fechas anteriores para el historial
            $oldStart = $task->finicio;
            $oldEnd = $task->ftermino;
            
            // Obtener el comentario si se proporcionó
            $comment = $request->input('comment', '');
            
            // Actualizar fechas
            $task->finicio = $request->start;
            $task->ftermino = $request->end;
            
            // Si hay comentario, añadirlo
            if (!empty($comment)) {
                $task->comentario = $comment;
            }
            
            $task->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Fechas actualizadas correctamente' . (!empty($comment) ? ' con comentario' : ''),
                'comment' => $comment,
                'task' => [
                    'id' => $task->id,
                    'start' => $task->finicio->format('Y-m-d'),
                    'end' => $task->ftermino->format('Y-m-d'),
                    'active' => $task->isActive()
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}