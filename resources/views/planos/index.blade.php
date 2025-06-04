<!-- filepath: resources/views/planos/index.blade.php -->
@extends('layouts.app')

@section('content')
<style>
    /* Contenedor principal */
    .planos-container {
        max-width: 1000px;
        margin: 40px auto;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.03);
        padding: 35px 30px;
    }

    /* Encabezado principal */
    .planos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .planos-title {
        font-size: 1.8rem;
        color: #333;
        margin: 0;
        font-weight: 500;
        letter-spacing: -0.5px;
    }

    .planos-btn {
        display: inline-flex;
        align-items: center;
        background: #3a3a3a;
        color: #fff;
        padding: 8px 18px 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .planos-btn:hover {
        background: #222;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
    
    /* Sección de búsqueda */
    .planos-search-section {
        margin-bottom: 25px;
    }
    
    .planos-actions-header {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .planos-search-form {
        flex: 1;
        min-width: 200px;
        max-width: 23%;
    }
    
    .search-container {
        display: flex;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #e0e0e0;
        background: #fff;
    }
    
    .planos-search-input {
        padding: 8px 10px;
        border: none;
        font-size: 0.85rem;
        width: 100%;
        background: transparent;
    }
    
    .planos-search-input:focus {
        outline: none;
    }
    
    .planos-search-btn {
        padding: 8px;
        background: transparent;
        border: none;
        color: #777;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .planos-search-btn:hover {
        background: #f5f5f5;
        color: #333;
    }

    /* Tabla */
    .table-wrapper {
        overflow-y: auto;
        max-height: 500px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .planos-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
    }

    .planos-table th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f7f7f7;
        color: #555;
        font-weight: 500;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid #eaeaea;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .planos-table th, .planos-table td {
        padding: 14px 18px;
    }

    .planos-table tbody tr {
        transition: all 0.15s ease-out;
        border-bottom: 1px solid #f5f5f5;
    }

    .planos-table tbody tr:hover {
        background: #f9f9f9;
        transform: scale(1.01);
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .planos-table tbody tr:last-child {
        border-bottom: none;
    }

    .planos-table td {
        color: #444;
        font-size: 0.95rem;
    }

    /* Campos de datos */
    .planos-id {
        color: #888;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .planos-codigo {
        font-weight: 500;
        color: #333;
    }

    .planos-ref {
        color: #666;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .planos-desc {
        color: #555;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Acciones */
    .planos-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    
    .planos-actions a,
    .planos-actions button {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
    }

    .planos-actions a.ver {
        background: #e9e9e9;
        color: #444;
    }

    .planos-actions a.ver:hover {
        background: #dedede;
        transform: translateY(-1px);
    }

    .planos-actions a.editar {
        background: #f2f2f2;
        color: #555;
    }

    .planos-actions a.editar:hover {
        background: #e7e7e7;
        transform: translateY(-1px);
    }

    .planos-actions button.eliminar {
        background: #fff;
        color: #777;
        border: 1px solid #eaeaea;
    }

    .planos-actions button.eliminar:hover {
        background: #f5f5f5;
        color: #555;
        transform: translateY(-1px);
    }

    .empty-message {
        padding: 40px 0;
        text-align: center;
        color: #999;
        font-style: italic;
        background: #fbfbfb;
    }
</style>

<div class="planos-container">
    <div class="planos-header">
        <h1 class="planos-title">Lista de Planos</h1>
        <a href="{{ route('planos.create') }}" class="planos-btn">Nuevo Plano</a>
    </div>
    
    <div class="planos-search-section">
        <div class="planos-actions-header">
            <form action="{{ route('planos.index') }}" method="GET" class="planos-search-form">
                <div class="search-container">
                    <input type="text" name="search_id" placeholder="ID" class="planos-search-input" value="{{ $request->search_id ?? '' }}">
                    <button type="submit" class="planos-search-btn">🔎</button>
                </div>
            </form>
            <form action="{{ route('planos.index') }}" method="GET" class="planos-search-form">
                <div class="search-container">
                    <input type="text" name="search_codigo" placeholder="Código" class="planos-search-input" value="{{ $request->search_codigo ?? '' }}">
                    <button type="submit" class="planos-search-btn">🔎</button>
                </div>
            </form>
            <form action="{{ route('planos.index') }}" method="GET" class="planos-search-form">
                <div class="search-container">
                    <input type="text" name="search_ref" placeholder="Referencia" class="planos-search-input" value="{{ $request->search_ref ?? '' }}">
                    <button type="submit" class="planos-search-btn">🔎</button>
                </div>
            </form>
            <form action="{{ route('planos.index') }}" method="GET" class="planos-search-form">
                <div class="search-container">
                    <input type="text" name="search_desc" placeholder="Descripción" class="planos-search-input" value="{{ $request->search_desc ?? '' }}">
                    <button type="submit" class="planos-search-btn">🔍</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="planos-table">
            <thead>
                <tr>
                    <th width="60">ID</th>
                    <th width="120">Código</th>
                    <th width="120">Referencia</th>
                    <th>Descripción</th>
                    <th width="220">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planos as $plano)
                    <tr>
                        <td class="planos-id">{{ $plano->id }}</td>
                        <td class="planos-codigo">{{ $plano->codigo }}</td>
                        <td class="planos-ref">{{ $plano->ref_np }}</td>
                        <td class="planos-desc">{{ $plano->descripcion }}</td>
                        <td>
                            <div class="planos-actions">
                                @if($plano->ruta)
                                    <a href="{{ $plano->ruta }}" class="archivo" title="Ver archivo" target="_blank">Archivo</a>
                                @endif
                                <a href="{{ route('planos.show', $plano) }}" class="ver" title="Ver detalles">Ver</a>
                                <a href="{{ route('planos.edit', $plano) }}" class="editar" title="Editar plano">Editar</a>
                                
                                <form action="{{ route('planos.destroy', $plano) }}" method="POST" onsubmit="return confirm('¿Confirma que desea eliminar este plano?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="eliminar" title="Eliminar plano">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-message">No hay planos registrados en el sistema.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection