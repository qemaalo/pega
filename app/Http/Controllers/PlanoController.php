<?php
namespace App\Http\Controllers;

use App\Models\Plano;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Plano::query();
        
        if ($request->has('search_id') && !empty($request->search_id)) {
            $query->where('id', 'like', '%' . $request->search_id . '%');
        }
    
        if ($request->has('search_codigo') && !empty($request->search_codigo)) {
            $query->where('codigo', 'like', '%' . $request->search_codigo . '%');
        }
        
        if ($request->has('search_ref') && !empty($request->search_ref)) {
            $query->where('ref_np', 'like', '%' . $request->search_ref . '%');
        }
        
        if ($request->has('search_desc') && !empty($request->search_desc)) {
            $query->where('descripcion', 'like', '%' . $request->search_desc . '%');
        }

        $planos = $query->get();
        
        return view('planos.index', compact('planos', 'request'));
    }

    public function create()
    {
        return view('planos.create');
    }

    public function store(Request $request)
    {
        Plano::create($request->all());
        return redirect()->route('planos.index');
    }

    public function show(Plano $plano)
    {
        return view('planos.show', compact('plano'));
    }

    public function edit(Plano $plano)
    {
        return view('planos.edit', compact('plano'));
    }

    public function update(Request $request, Plano $plano)
    {
        $plano->update($request->all());
        return redirect()->route('planos.index');
    }

    public function destroy(Plano $plano)
    {
        $plano->delete();
        return redirect()->route('planos.index');
    }
}