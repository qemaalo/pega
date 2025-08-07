<?php

namespace App\Http\Controllers;

use App\Models\Compromops;
use App\Models\Centro;
use App\Models\Maquinaria;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        // Determinar el semestre y año a mostrar
        $semester = $request->semester ? (int)$request->semester : (date('n') <= 6 ? 1 : 2);
        $year = $request->year ? (int)$request->year : date('Y');
        
        // Calcular fechas del semestre
        if ($semester == 1) {
            // Primer semestre: Enero - Junio
            $startDate = Carbon::createFromDate($year, 1, 1);
            $endDate = Carbon::createFromDate($year, 6, 30);
        } else {
            // Segundo semestre: Julio - Diciembre
            $startDate = Carbon::createFromDate($year, 7, 1);
            $endDate = Carbon::createFromDate($year, 12, 31);
        }
        
        // Calcular total de días del semestre
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Obtener las tareas (órdenes de compromiso) con sus maquinarias
        $query = Compromops::with(['maquinaria.centro']);
        
        // Si estamos buscando por OP
        if ($request->has('search_op') && !empty($request->search_op)) {
            $search_term = trim($request->search_op);
            
            // Buscar exactamente el OP o similar
            $query->where(function($q) use ($search_term) {
                $q->where('op', $search_term)
                  ->orWhere('op', 'LIKE', "%{$search_term}%");
            });
            
            // Si se encontró algo y no se especificó un semestre, ajustar el semestre a la primera tarea
            if (!$request->has('semester')) {
                $monthQuery = Compromops::query();
                $monthQuery->where(function($q) use ($search_term) {
                    $q->where('op', $search_term)
                      ->orWhere('op', 'LIKE', "%{$search_term}%");
                });
                
                $firstTask = $monthQuery->first();
                if ($firstTask && $firstTask->finicio) {
                    $taskDate = Carbon::parse($firstTask->finicio);
                    $semester = $taskDate->month <= 6 ? 1 : 2;
                    $year = $taskDate->year;
                    
                    // Recalcular fechas del semestre
                    if ($semester == 1) {
                        $startDate = Carbon::createFromDate($year, 1, 1);
                        $endDate = Carbon::createFromDate($year, 6, 30);
                    } else {
                        $startDate = Carbon::createFromDate($year, 7, 1);
                        $endDate = Carbon::createFromDate($year, 12, 31);
                    }
                    $totalDays = $startDate->diffInDays($endDate) + 1;
                }
            }
        } else {
            // Filtrar por semestre
            $query->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('finicio', [$startDate->startOfDay(), $endDate->endOfDay()])
                  ->orWhereBetween('ftermino', [$startDate->startOfDay(), $endDate->endOfDay()])
                  ->orWhere(function($sq) use ($startDate, $endDate) {
                      $sq->where('finicio', '<=', $startDate->startOfDay())
                         ->where('ftermino', '>=', $endDate->endOfDay());
                  });
            });
        }
        
        // Obtener maquinarias agrupadas por centro (para el sidebar)
        $centrosConMaquinarias = Centro::with(['maquinarias' => function($query) {
            $query->active()->orderBy('orden');
        }])
        ->active()
        ->whereIn('descripcion', ['PRENSA', 'REVESTIMIENTO', 'POLIURETANO', 'TRAFILA', 'ANILLOS'])
        ->orderBy('descripcion')
        ->get();
        
        // Crear string descriptivo del semestre
        $semesterString = $semester == 1 ? '1° Semestre' : '2° Semestre';
        $dateString = $semesterString . ' ' . $year;
        
        // Retornar vista con datos del semestre
        return view('compromops.index', [
            'tasks' => $query->get(),
            'centros' => $centrosConMaquinarias,
            'currentSemester' => $semester,
            'currentYear' => $year,
            'totalDays' => $totalDays,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateString' => $dateString,
            'isSearchResults' => !empty($request->search_op)
        ]);
    }
    
    // Mantener store vacío para no romper las rutas resource
    public function store(Request $request)
    {
        return redirect()->route('compromops.index')->with('error', 'La creación de tareas está desactivada');
    }
    
    public function update(Request $request, $id)
    {
        // Si es una petición AJAX simple para actualizar fechas
        if ($request->ajax() || $request->wantsJson()) {
            $task = Compromops::findOrFail($id);
            $task->update([
                'finicio' => $request->finicio,
                'ftermino' => $request->ftermino
            ]);
            
            return response()->json(['success' => true, 'task' => $task]);
        }
        
        // Para actualizaciones completas del formulario
        $rules = [
            'op' => 'required',
            'np' => 'required',
            'linea' => 'required',
            'usuario' => 'required',
            'finicio' => 'required|date',
            'ftermino' => 'required|date|after_or_equal:finicio',
        ];
        
        $request->validate($rules);
        
        $task = Compromops::findOrFail($id);
        $task->update($request->all());
        
        return redirect()->route('compromops.index')->with('success', 'Tarea actualizada exitosamente.');
    }
    
    public function destroy($id)
    {
        $task = Compromops::findOrFail($id);
        $task->delete();
        
        return redirect()->route('compromops.index')->with('success', 'Tarea eliminada exitosamente.');
    }
    
    public function searchByOp($op)
    {
        $task = Compromops::where('op', $op)->first();
        
        if (!$task) {
            return redirect()->route('compromops.index')->with('error', 'No se encontró la tarea con OP: ' . $op);
        }
        
        // Redirigir al mes correspondiente a la fecha de inicio de la tarea
        $month = $task->finicio ? date('n', strtotime($task->finicio)) : date('n');
        $year = $task->finicio ? date('Y', strtotime($task->finicio)) : date('Y');
        
        return redirect()->route('compromops.index', ['month' => $month, 'year' => $year, 'search_op' => $op]);
    }
    
    /**
     * Método específico para actualizaciones AJAX de fechas
     */
    public function ajaxUpdate(Request $request, $id)
    {
        try {
            // Buscar la tarea
            $task = Compromops::findOrFail($id);
            
            // Preparar datos para actualizar
            $updateData = [
                'finicio' => $request->finicio,
                'ftermino' => $request->ftermino
            ];
            
            // Si se está actualizando la maquinaria, verificar conflictos
            if ($request->has('maquinaria_id') && $request->maquinaria_id != $task->maquinaria_id) {
                $maquinariaId = $request->maquinaria_id;
                
                // Verificar conflictos con otras tareas en la misma maquinaria
                $conflicto = Compromops::where('maquinaria_id', $maquinariaId)
                    ->where('id', '!=', $id)
                    ->where(function($query) use ($request) {
                        $query->whereBetween('finicio', [$request->finicio, $request->ftermino])
                              ->orWhereBetween('ftermino', [$request->finicio, $request->ftermino])
                              ->orWhere(function($q) use ($request) {
                                  $q->where('finicio', '<=', $request->finicio)
                                    ->where('ftermino', '>=', $request->ftermino);
                              });
                    })
                    ->first();
                
                if ($conflicto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Conflicto: Ya existe una tarea en esta maquinaria en las fechas seleccionadas',
                        'conflicto' => [
                            'op' => $conflicto->op,
                            'linea' => $conflicto->linea,
                            'finicio' => $conflicto->finicio,
                            'ftermino' => $conflicto->ftermino
                        ]
                    ], 409);
                }
                
                $updateData['maquinaria_id'] = $maquinariaId;
            }
            
            // Actualizar la tarea
            $task->update($updateData);
            
            // Devolver respuesta JSON exitosa
            return response()->json([
                'success' => true,
                'message' => 'Tarea actualizada correctamente',
                'task' => $task->load('maquinaria.centro')
            ]);
        } catch (\Exception $e) {
            // Devolver error como JSON
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Guarda un comentario para una tarea
     */
    public function saveComment(Request $request, $id)
    {
        try {
            // Validar datos
            $request->validate([
                'comment' => 'required|string|max:1000',
            ]);
            
            // Buscar la tarea
            $task = Compromops::findOrFail($id);
            
            // Crear comentario sin las fechas
            $comment = $task->comments()->create([
                'comentario' => $request->comment,
                'usuario' => auth()->user() ? auth()->user()->name : 'Usuario anónimo',
            ]);
            
            // Actualizar fechas de la tarea (opcional)
            if ($request->has('finicio') && $request->has('ftermino')) {
                $task->update([
                    'finicio' => $request->finicio,
                    'ftermino' => $request->ftermino
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Comentario guardado correctamente',
                'comment' => $comment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar comentario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar la maquinaria asignada a una tarea
     */
    public function updateMaquinaria(Request $request, $id)
    {
        try {
            $task = Compromops::findOrFail($id);
            $maquinariaId = $request->maquinaria_id;
            
            // Verificar si hay conflicto (otra tarea en la misma maquinaria en las mismas fechas)
            $conflicto = Compromops::where('maquinaria_id', $maquinariaId)
                ->where('id', '!=', $id)
                ->where(function($query) use ($task) {
                    $query->whereBetween('finicio', [$task->finicio, $task->ftermino])
                          ->orWhereBetween('ftermino', [$task->finicio, $task->ftermino])
                          ->orWhere(function($q) use ($task) {
                              $q->where('finicio', '<=', $task->finicio)
                                ->where('ftermino', '>=', $task->ftermino);
                          });
                })
                ->first();
            
            if ($conflicto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conflicto: Ya existe una tarea en esta maquinaria en las fechas seleccionadas',
                    'conflicto' => [
                        'op' => $conflicto->op,
                        'linea' => $conflicto->linea,
                        'finicio' => $conflicto->finicio,
                        'ftermino' => $conflicto->ftermino
                    ]
                ], 409);
            }
            
            // Actualizar la maquinaria
            $task->update(['maquinaria_id' => $maquinariaId]);
            
            return response()->json([
                'success' => true,
                'message' => 'Maquinaria actualizada correctamente',
                'task' => $task->load('maquinaria.centro')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar maquinaria: ' . $e->getMessage()
            ], 500);
        }
    }
}