<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cumpleano;

class CumpleanoController extends Controller
{
    /**
     * Mostrar la lista de cumpleaños
     */
    public function index()
    {
        $cumpleanos = Cumpleano::all()->sortBy(function($cumple) {
            return $cumple->dias_restantes;
        });
        
        return view('cumpleanos.cumple', compact('cumpleanos'));
    }

    /**
     * Guardar un nuevo cumpleaños
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rut' => 'required|string|unique:cumpleanos,rut|max:20',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'fecha_cumpleanos' => 'required|date',
            'vinculado_empresa' => 'sometimes|boolean',
        ], [
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'Este RUT ya está registrado.',
            'rut.max' => 'El RUT no puede tener más de 20 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede tener más de 100 caracteres.',
            'fecha_cumpleanos.required' => 'La fecha de cumpleaños es obligatoria.',
            'fecha_cumpleanos.date' => 'La fecha debe ser válida.',
        ]);

        try {
            Cumpleano::create([
                'rut' => $validated['rut'],
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'fecha_cumpleanos' => $validated['fecha_cumpleanos'],
                'vinculado_empresa' => $request->has('vinculado_empresa'),
                'email_enviado' => false,
            ]);

            return redirect()->route('cumpleanos.index')
                           ->with('success', 'Cumpleaños agregado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al guardar el cumpleaños: ' . $e->getMessage());
        }
    }

    /**
     * Marcar email como enviado
     */
    public function marcarEnviado($id)
    {
        try {
            $cumpleano = Cumpleano::findOrFail($id);
            $cumpleano->email_enviado = true;
            $cumpleano->save();

            return redirect()->route('cumpleanos.index')
                           ->with('success', 'Email marcado como enviado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('cumpleanos.index')
                           ->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Desvincular empleado de la empresa
     */
    public function desvincular($id)
    {
        try {
            $cumpleano = Cumpleano::findOrFail($id);
            $cumpleano->vinculado_empresa = false;
            $cumpleano->save();

            return redirect()->route('cumpleanos.index')
                           ->with('success', $cumpleano->nombre_completo . ' ha sido desvinculado de la empresa.');
        } catch (\Exception $e) {
            return redirect()->route('cumpleanos.index')
                           ->with('error', 'Error al desvincular empleado: ' . $e->getMessage());
        }
    }

    /**
     * Vincular empleado a la empresa
     */
    public function vincular($id)
    {
        try {
            $cumpleano = Cumpleano::findOrFail($id);
            $cumpleano->vinculado_empresa = true;
            $cumpleano->save();

            return redirect()->route('cumpleanos.index')
                           ->with('success', $cumpleano->nombre_completo . ' ha sido vinculado a la empresa.');
        } catch (\Exception $e) {
            return redirect()->route('cumpleanos.index')
                           ->with('error', 'Error al vincular empleado: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un cumpleaños
     */
    public function destroy($id)
    {
        try {
            $cumpleano = Cumpleano::findOrFail($id);
            $nombre = $cumpleano->nombre_completo;
            $cumpleano->delete();

            return redirect()->route('cumpleanos.index')
                           ->with('success', 'Registro de ' . $nombre . ' eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('cumpleanos.index')
                           ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }
}
