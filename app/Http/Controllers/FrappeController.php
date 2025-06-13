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
                // Asegurar que las fechas sean instancias de Carbon
                $startDate = $task->finicio instanceof Carbon ? $task->finicio : Carbon::parse($task->finicio);
                $endDate = $task->ftermino instanceof Carbon ? $task->ftermino : Carbon::parse($task->ftermino);
                
                return [
                    'id' => (string) $task->id,
                    'name' => 'OP ' . $task->op,
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'progress' => $task->fterminoreal ? 100 : 0,
                    'dependencies' => '',
                    'readonly' => true// se mueven igual
                ];
            });

            return response()->json($formattedTasks);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 