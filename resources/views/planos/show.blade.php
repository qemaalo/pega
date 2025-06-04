<!-- filepath: resources/views/planos/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="planos-container">
    <div class="planos-header">
        <h1 class="planos-title">Detalle del Plano</h1>
        <div class="planos-actions-top">
            <a href="{{ route('planos.index') }}" class="planos-btn-secondary">Volver a la lista</a>
            <a href="{{ route('planos.edit', $plano) }}" class="planos-btn">Editar Plano</a>
        </div>
    </div>
    
    <div class="plano-detail-card">
        <div class="plano-detail-header">
            <div class="plano-badge">ID: {{ $plano->id }}</div>
            <h2 class="plano-codigo-title">{{ $plano->codigo }}</h2>
        </div>
        
        <div class="plano-detail-section">
            <div class="plano-detail-row">
                <div class="plano-detail-label">Descripción</div>
                <div class="plano-detail-value">{{ $plano->descripcion }}</div>
            </div>
            
            <div class="plano-detail-row">
                <div class="plano-detail-label">Tipo de Plano</div>
                <div class="plano-detail-value">{{ $plano->tipo_plano }}</div>
            </div>
            
            <div class="plano-detail-row">
                <div class="plano-detail-label">Nombre del Plano</div>
                <div class="plano-detail-value">{{ $plano->nombre_plano }}</div>
            </div>
            
            <div class="plano-detail-row">
                <div class="plano-detail-label">Versión</div>
                <div class="plano-detail-value">{{ $plano->vercion }}</div>
            </div>
            
            <div class="plano-detail-row">
                <div class="plano-detail-label">Revisión</div>
                <div class="plano-detail-value">{{ $plano->rev }}</div>
            </div>
            
            @if($plano->comentario)
            <div class="plano-detail-row">
                <div class="plano-detail-label">Comentario</div>
                <div class="plano-detail-value plano-comment">{{ $plano->comentario }}</div>
            </div>
            @endif
        </div>
        
        <div class="plano-detail-section">
            <h3 class="plano-section-title">Archivos Asociados</h3>
            
            @if($plano->nombre_plano)
            <div class="plano-detail-row">
                <div class="plano-detail-label">Archivo Principal</div>
                <div class="plano-detail-value plano-file">
                    <span class="plano-file-name">{{ $plano->nombre_plano }}</span>
                    <span class="plano-file-ext">.{{ $plano->ext }}</span>
                </div>
            </div>
            @endif
            
            @if($plano->nombre_dwg)
            <div class="plano-detail-row">
                <div class="plano-detail-label">Archivo DWG</div>
                <div class="plano-detail-value plano-file">
                    <span class="plano-file-name">{{ $plano->nombre_dwg }}</span>
                    <span class="plano-file-ext">.{{ $plano->ext_dwg }}</span>
                </div>
            </div>
            @endif
        </div>
        
        <div class="plano-detail-section">
            <h3 class="plano-section-title">Estado</h3>
            
            <div class="plano-detail-row">
                <div class="plano-detail-label">Activo</div>
                <div class="plano-detail-value">
                    <span class="plano-status {{ $plano->activo ? 'active' : 'inactive' }}">
                        {{ $plano->activo ? 'Sí' : 'No' }}
                    </span>
                </div>
            </div>
            
            <div class="plano-detail-row">
                <div class="plano-detail-label">Válido</div>
                <div class="plano-detail-value">
                    <span class="plano-status {{ $plano->valido ? 'active' : 'inactive' }}">
                        {{ $plano->valido ? 'Sí' : 'No' }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="plano-footer">
            <form action="{{ route('planos.destroy', $plano) }}" method="POST" onsubmit="return confirm('¿Confirma que desea eliminar este plano?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="planos-btn-danger">Eliminar Plano</button>
            </form>
        </div>
    </div>
</div>

<style>
    .planos-container {
        max-width: 1000px;
        margin: 40px auto;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.03);
        padding: 35px 30px;
    }
    
    .planos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
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
    
    .planos-actions-top {
        display: flex;
        gap: 10px;
    }
    
    .planos-btn {
        display: inline-flex;
        align-items: center;
        background: #3a3a3a;
        color: #fff;
        padding: 8px 18px;
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
    
    .planos-btn-secondary {
        display: inline-flex;
        align-items: center;
        background: #f5f5f5;
        color: #555;
        padding: 8px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
    }
    
    .planos-btn-secondary:hover {
        background: #e8e8e8;
    }
    
    .planos-btn-danger {
        display: inline-flex;
        align-items: center;
        background: #fff;
        color: #d32f2f;
        padding: 8px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
        border: 1px solid #ffcdd2;
        cursor: pointer;
    }
    
    .planos-btn-danger:hover {
        background: #fef8f8;
        border-color: #ef9a9a;
    }
    
    .plano-detail-card {
        background: #fbfbfb;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .plano-detail-header {
        background: #f5f5f5;
        padding: 20px 25px;
        border-bottom: 1px solid #eaeaea;
    }
    
    .plano-badge {
        display: inline-block;
        background: #e0e0e0;
        color: #555;
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 4px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .plano-codigo-title {
        margin: 0;
        color: #333;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .plano-detail-section {
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
    }
    
    .plano-section-title {
        font-size: 1.1rem;
        color: #444;
        margin: 0 0 15px 0;
        font-weight: 500;
    }
    
    .plano-detail-row {
        display: flex;
        margin-bottom: 12px;
    }
    
    .plano-detail-row:last-child {
        margin-bottom: 0;
    }
    
    .plano-detail-label {
        width: 180px;
        color: #777;
        font-size: 0.95rem;
    }
    
    .plano-detail-value {
        flex: 1;
        color: #333;
        font-size: 0.95rem;
    }
    
    .plano-comment {
        background: #f9f9f9;
        padding: 10px;
        border-radius: 4px;
        border-left: 3px solid #e0e0e0;
    }
    
    .plano-file {
        display: flex;
        align-items: center;
    }
    
    .plano-file-name {
        font-weight: 500;
    }
    
    .plano-file-ext {
        color: #777;
        margin-left: 2px;
    }
    
    .plano-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .plano-status.active {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .plano-status.inactive {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .plano-footer {
        padding: 20px 25px;
        display: flex;
        justify-content: flex-end;
    }
</style>
@endsection