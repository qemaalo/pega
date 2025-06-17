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
                $isActive = $task->activo ?? true;
                
                return [
                    'id' => (string) $task->id,
                    'name' => 'OP ' . $task->op,
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'progress' => $task->fterminoreal ? 100 : 0,
                    'dependencies' => '',
                    'readonly' => !$isActive
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
            
            // Verificar si está activa
            if (!$task->activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede modificar una tarea inactiva'
                ], 400);
            }
            
            // Obtener el comentario si se proporcionó
            $task->comentario = $comment = $request->input('comment', '');
            
            // Guardar las fechas anteriores para el historial
            $oldStart = $task->finicio;
            $oldEnd = $task->ftermino;
            
            // Actualizar fechas
            $task->finicio = $request->start;
            $task->ftermino = $request->end;
            
            // Si hay comentario, añadirlo a un campo de comentarios o historial
            // Aquí puedes decidir cómo quieres almacenar los comentarios
            // Por ejemplo, en un campo 'comentarios' o en una tabla separada de historial
            if (!empty($comment)) {
                // Opción 1: Guardar en un campo de comentarios (si existe)
                if (isset($task->comentarios)) {
                    $task->comentarios = $comment;
                }
                
                // Opción 2: Crear un registro de historial (si tienes una tabla de historial)
                // HistorialCambios::create([
                //     'compromop_id' => $task->id,
                //     'fecha_anterior_inicio' => $oldStart,
                //     'fecha_anterior_fin' => $oldEnd,
                //     'fecha_nueva_inicio' => $request->start,
                //     'fecha_nueva_fin' => $request->end,
                //     'comentario' => $comment,
                //     'usuario_id' => auth()->id(),
                //     'fecha_cambio' => now()
                // ]);
            }
            
            $task->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Fechas actualizadas correctamente' . (!empty($comment) ? ' con comentario' : ''),
                'comment' => $comment
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}