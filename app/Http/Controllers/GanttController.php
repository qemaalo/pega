<?php

namespace App\Http\Controllers;

use App\Models\Compromops;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        // Determinar el mes y año a mostrar
        $month = $request->month ? (int)$request->month : date('n');
        $year = $request->year ? (int)$request->year : date('Y');
        
        // Crear fecha para el primer día del mes seleccionado
        $date = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        
        // Obtener las tareas (órdenes de compromiso)
        $query = Compromops::query();
        
        // Filtrar por ID/OP si se proporciona
        if ($request->has('search_op') && !empty($request->search_op)) {
            $query->where('op', $request->search_op);
        }
        
        // Obtener tareas que se cruzan con el mes seleccionado
        $query->where(function($q) use ($year, $month) {
            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
            
            // Tareas que comienzan, terminan o abarcan el mes seleccionado
            $q->whereBetween('finicio', [$startOfMonth, $endOfMonth])
              ->orWhereBetween('ftermino', [$startOfMonth, $endOfMonth])
              ->orWhere(function($sq) use ($startOfMonth, $endOfMonth) {
                  $sq->where('finicio', '<=', $startOfMonth)
                     ->where('ftermino', '>=', $endOfMonth);
              });
        });
        
        $tasks = $query->get();
        
        return view('compromops.index', compact('tasks', 'daysInMonth', 'request'));
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
            
            // Actualizar fechas
            $task->update([
                'finicio' => $request->finicio,
                'ftermino' => $request->ftermino
            ]);
            
            // Devolver respuesta JSON exitosa
            return response()->json([
                'success' => true,
                'message' => 'Fechas actualizadas correctamente',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            // Devolver error como JSON
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }
}