<!-- filepath: resources/views/planos/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="planos-container">
    <div class="planos-header">
        <h1 class="planos-title">Editar Plano</h1>
        <a href="{{ route('planos.index') }}" class="planos-btn-secondary">Volver a la lista</a>
    </div>
    
    <div class="plano-form-card">
        <form action="{{ route('planos.update', $plano) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="plano-form-section">
                <h3 class="plano-section-title">Información General</h3>
                
                <div class="plano-form-row">
                    <div class="plano-form-group">
                        <label for="codigo" class="plano-form-label">Código <span class="required">*</span></label>
                        <input type="text" id="codigo" name="codigo" class="plano-form-input" value="{{ $plano->codigo }}" required>
                    </div>
                    
                    <div class="plano-form-group">
                        <label for="tipo_plano" class="plano-form-label">Tipo de Plano</label>
                        <input type="text" id="tipo_plano" name="tipo_plano" class="plano-form-input" value="{{ $plano->tipo_plano }}">
                    </div>
                </div>
                
                <div class="plano-form-row">
                    <div class="plano-form-group full-width">
                        <label for="descripcion" class="plano-form-label">Descripción <span class="required">*</span></label>
                        <input type="text" id="descripcion" name="descripcion" class="plano-form-input" value="{{ $plano->descripcion }}" required>
                    </div>
                </div>
                
                <div class="plano-form-row">
                    <div class="plano-form-group">
                        <label for="vercion" class="plano-form-label">Versión</label>
                        <input type="text" id="vercion" name="vercion" class="plano-form-input" value="{{ $plano->vercion }}">
                    </div>
                    
                    <div class="plano-form-group">
                        <label for="rev" class="plano-form-label">Revisión</label>
                        <input type="text" id="rev" name="rev" class="plano-form-input" value="{{ $plano->rev }}">
                    </div>
                    
                    <div class="plano-form-group">
                        <label for="orden" class="plano-form-label">Orden</label>
                        <input type="number" id="orden" name="orden" class="plano-form-input" value="{{ $plano->orden }}">
                    </div>
                </div>
            </div>
            
            <div class="plano-form-section">
                <h3 class="plano-section-title">Archivos</h3>
                
                <div class="plano-form-row">
                    <div class="plano-form-group">
                        <label for="nombre_plano" class="plano-form-label">Nombre del Plano</label>
                        <input type="text" id="nombre_plano" name="nombre_plano" class="plano-form-input" value="{{ $plano->nombre_plano }}">
                    </div>
                    
                    <div class="plano-form-group">
                        <label for="ext" class="plano-form-label">Extensión</label>
                        <input type="text" id="ext" name="ext" class="plano-form-input" value="{{ $plano->ext }}">
                    </div>
                </div>
                
                <div class="plano-form-row">
                    <div class="plano-form-group">
                        <label for="nombre_dwg" class="plano-form-label">Nombre del Archivo DWG</label>
                        <input type="text" id="nombre_dwg" name="nombre_dwg" class="plano-form-input" value="{{ $plano->nombre_dwg }}">
                    </div>
                    
                    <div class="plano-form-group">
                        <label for="ext_dwg" class="plano-form-label">Extensión DWG</label>
                        <input type="text" id="ext_dwg" name="ext_dwg" class="plano-form-input" value="{{ $plano->ext_dwg }}">
                    </div>
                </div>
            </div>
            
            <div class="plano-form-section">
                <h3 class="plano-section-title">Comentarios</h3>
                
                <div class="plano-form-row">
                    <div class="plano-form-group full-width">
                        <label for="comentario" class="plano-form-label">Comentario</label>
                        <textarea id="comentario" name="comentario" class="plano-form-textarea" rows="3">{{ $plano->comentario }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="plano-form-section">
                <h3 class="plano-section-title">Estado</h3>
                
                <div class="plano-form-row">
                    <div class="plano-form-group checkbox-group">
                        <label class="plano-checkbox-container">
                            <input type="checkbox" name="activo" value="1" {{ $plano->activo ? 'checked' : '' }}>
                            <span class="plano-checkbox-label">Activo</span>
                        </label>
                    </div>
                    
                    <div class="plano-form-group checkbox-group">
                        <label class="plano-checkbox-container">
                            <input type="checkbox" name="valido" value="1" {{ $plano->valido ? 'checked' : '' }}>
                            <span class="plano-checkbox-label">Válido</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="plano-form-footer">
                <a href="{{ route('planos.show', $plano) }}" class="planos-btn-secondary">Cancelar</a>
                <button type="submit" class="planos-btn">Guardar Cambios</button>
            </div>
        </form>
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
        border: none;
    }
    
    .planos-btn-secondary:hover {
        background: #e8e8e8;
    }
    
    .plano-form-card {
        background: #fbfbfb;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .plano-form-section {
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
    }
    
    .plano-section-title {
        font-size: 1.1rem;
        color: #444;
        margin: 0 0 15px 0;
        font-weight: 500;
    }
    
    .plano-form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .plano-form-row:last-child {
        margin-bottom: 0;
    }
    
    .plano-form-group {
        flex: 1;
        min-width: 200px;
    }
    
    .plano-form-group.full-width {
        flex-basis: 100%;
    }
    
    .plano-form-group.checkbox-group {
        flex: 0 0 auto;
    }
    
    .plano-form-label {
        display: block;
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 6px;
        font-weight: 500;
    }
    
    .plano-form-input, .plano-form-textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.95rem;
        color: #333;
        transition: border-color 0.2s;
        background: #fff;
    }
    
    .plano-form-input:focus, .plano-form-textarea:focus {
        border-color: #aaa;
        outline: none;
    }
    
    .plano-form-textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .plano-checkbox-container {
        display: flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    
    .plano-checkbox-label {
        margin-left: 6px;
        font-size: 0.95rem;
        color: #444;
    }
    
    .plano-form-footer {
        padding: 20px 25px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #f5f5f5;
    }
    
    .required {
        color: #d32f2f;
    }
</style>
@endsection