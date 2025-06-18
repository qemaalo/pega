@extends('layouts.app')

@section('content')
<div class="container">
    <div class="header-section">
        <div class="header-content">
            <h1>Gestión de Cumpleaños</h1>
            <button type="button" class="btn btn-primary" onclick="openModal()">
                <span class="btn-icon">+</span>
                Agregar Cumpleaños
            </button>
        </div>
    </div>
    
    <!-- Filtros de búsqueda -->
    <div class="filters-card">
        <h3>Filtros de Búsqueda</h3>
        <div class="filters-content">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search-nombre">Buscar por nombre:</label>
                    <input type="text" id="search-nombre" placeholder="Escriba el nombre..." class="filter-input">
                </div>
                
                <div class="filter-group">
                    <label for="search-rut">Buscar por RUT:</label>
                    <input type="text" id="search-rut" placeholder="12.345.678-9" class="filter-input">
                </div>
                
                <div class="filter-group">
                    <button type="button" id="clear-filters" class="btn btn-secondary">
                        <span class="btn-icon">🗑️</span>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de cumpleaños -->
    <div class="table-card">
        <div class="table-header">
            <h3>Lista de Cumpleaños</h3>
            <span class="record-count">Total: <span id="total-records">{{ $cumpleanos->count() }}</span> registros</span>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="cumpleanos-table">
                <thead>
                    <tr>
                        <th class="col-rut">RUT</th>
                        <th class="col-nombre">Nombre</th>
                        <th class="col-fecha">Nacimiento</th>
                        <th class="col-edad">Edad</th>
                        <th class="col-vinculado">Vinculado</th>
                        <th class="col-email">Email</th>
                        <th class="col-proximo">Días Restantes</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cumpleanos as $cumple)
                        <tr class="table-row" 
                            data-nombre="{{ strtolower($cumple->nombre_completo) }}"
                            data-rut="{{ strtolower(str_replace(['.', '-', ' '], '', $cumple->rut)) }}"
                            data-rut-display="{{ strtolower($cumple->rut) }}"
                            data-vinculado="{{ $cumple->vinculado_empresa ? 'empresa' : 'externo' }}"
                            data-email="{{ $cumple->email_enviado ? 'enviado' : 'pendiente' }}"
                            data-mes="{{ \Carbon\Carbon::parse($cumple->fecha_cumpleanos)->month }}">
                            <td class="cell-rut">
                                <span class="rut-text">{{ $cumple->rut }}</span>
                            </td>
                            <td class="cell-nombre">
                                <div class="nombre-container">
                                    <span class="nombre-text">{{ $cumple->nombre_completo }}</span>
                                </div>
                            </td>
                            <td class="cell-fecha">
                                <span class="fecha-text">
                                    {{ \Carbon\Carbon::parse($cumple->fecha_cumpleanos)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="cell-edad">
                                <span class="edad-text">{{ $cumple->edad_actual }}</span>
                            </td>
                            <td class="cell-vinculado">
                                @if($cumple->vinculado_empresa)
                                    <span class="badge vinculado-si">Empresa</span>
                                @else
                                    <span class="badge vinculado-no">Externo</span>
                                @endif
                            </td>
                            <td class="cell-email">
                                @if($cumple->email_enviado)
                                    <span class="badge email-enviado">Enviado</span>
                                @else
                                    <span class="badge email-pendiente">Pendiente</span>
                                @endif
                            </td>
                            <td class="cell-proximo">
                                @php
                                    $diasRestantes = $cumple->dias_restantes;
                                @endphp
                                
                                @if($diasRestantes == 0)
                                    <div class="dias-contador hoy">
                                        <span class="dias-numero">¡HOY!</span>
                                        <span class="dias-emoji">🎉</span>
                                    </div>
                                @elseif($diasRestantes <= 7)
                                    <div class="dias-contador proximos">
                                        <span class="dias-numero">{{ $diasRestantes }}</span>
                                        <span class="dias-texto">días</span>
                                    </div>
                                @elseif($diasRestantes <= 30)
                                    <div class="dias-contador upcoming">
                                        <span class="dias-numero">{{ $diasRestantes }}</span>
                                        <span class="dias-texto">días</span>
                                    </div>
                                @else
                                    <div class="dias-contador normal">
                                        <span class="dias-numero">{{ $diasRestantes }}</span>
                                        <span class="dias-texto">días</span>
                                    </div>
                                @endif
                            </td>
                            <td class="cell-acciones">
                                <div class="actions-container">
                                    {{-- Botón de email enviado --}}
                                    @if(!$cumple->email_enviado)
                                        <form method="POST" action="{{ route('cumpleanos.enviado', $cumple->id) }}" 
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-action btn-success"
                                                    onclick="return confirm('¿Marcar email como enviado?')"
                                                    title="Marcar como enviado">
                                                <span class="action-icon">✓</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="action-disabled" title="Email ya enviado">
                                            <span class="action-icon">✓</span>
                                        </span>
                                    @endif

                                    {{-- Botón de desvinculación/vinculación CORREGIDO --}}
                                    @if($cumple->vinculado_empresa)
                                        <form method="POST" action="{{ route('cumpleanos.desvincular', $cumple->id) }}" 
                                              style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-action btn-warning"
                                                    onclick="return confirm('¿Desvincular a {{ $cumple->nombre_completo }} de la empresa?')"
                                                    title="Desvincular de la empresa">
                                                <span class="action-icon">🔗</span>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('cumpleanos.vincular', $cumple->id) }}" 
                                              style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-action btn-info"
                                                    onclick="return confirm('¿Vincular a {{ $cumple->nombre_completo }} a la empresa?')"
                                                    title="Vincular a la empresa">
                                                <span class="action-icon">🔗</span>
                                            </form>
                                    @endif

                                    {{-- Botón de eliminar --}}
                                    <form method="POST" action="{{ route('cumpleanos.destroy', $cumple->id) }}" 
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-danger"
                                                onclick="return confirm('¿Está seguro que desea eliminar a {{ $cumple->nombre_completo }}? Esta acción no se puede deshacer.')"
                                                title="Eliminar registro">
                                            <span class="action-icon">🗑️</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="8" class="empty-state">
                                No hay cumpleaños registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div id="no-results" class="no-results" style="display: none;">
                <div class="no-results-content">
                    <span class="no-results-icon">🔍</span>
                    <h4>No se encontraron resultados</h4>
                    <p>No hay registros que coincidan con la búsqueda.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar cumpleaños -->
<div id="modal-overlay" class="modal-overlay" onclick="closeModal()">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">
                <span class="modal-icon">🎂</span>
                Agregar Nuevo Cumpleaños
            </h3>
            <button type="button" class="modal-close" onclick="closeModal()">
                <span>&times;</span>
            </button>
        </div>
        
        <div class="modal-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="alert-header">
                        <span class="alert-icon">⚠</span>
                        <strong>Por favor corrige los siguientes errores:</strong>
                    </div>
                    <ul class="alert-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('cumpleanos.store') }}" class="modal-form" id="cumpleanos-form">
                @csrf
                <div class="form-grid">
                    <div class="input-group">
                        <label for="modal-rut" class="input-label">
                            <span class="label-text">RUT</span>
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" 
                               id="modal-rut" 
                               name="rut" 
                               class="form-input"
                               placeholder="12.345.678-9" 
                               value="{{ old('rut') }}" 
                               required>
                        <span class="input-helper">Formato: 12.345.678-9</span>
                    </div>
                    
                    <div class="input-group">
                        <label for="modal-nombre" class="input-label">
                            <span class="label-text">Nombre</span>
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" 
                               id="modal-nombre" 
                               name="nombre" 
                               class="form-input"
                               placeholder="Juan Antonio" 
                               value="{{ old('nombre') }}" 
                               required>
                    </div>
                    
                    <div class="input-group">
                        <label for="modal-apellido" class="input-label">
                            <span class="label-text">Apellido</span>
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" 
                               id="modal-apellido" 
                               name="apellido" 
                               class="form-input"
                               placeholder="Pérez González" 
                               value="{{ old('apellido') }}" 
                               required>
                    </div>
                    
                    <div class="input-group">
                        <label for="modal-fecha" class="input-label">
                            <span class="label-text">Fecha de Cumpleaños</span>
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="date" 
                               id="modal-fecha" 
                               name="fecha_cumpleanos" 
                               class="form-input"
                               value="{{ old('fecha_cumpleanos') }}" 
                               required>
                    </div>
                    
                    <div class="input-group checkbox-group">
                        <label class="checkbox-container">
                            <input type="checkbox" 
                                   name="vinculado_empresa" 
                                   value="1" 
                                   {{ old('vinculado_empresa') ? 'checked' : '' }}
                                   class="checkbox-input">
                            <span class="checkbox-checkmark"></span>
                            <span class="checkbox-text">Vinculado a la empresa</span>
                        </label>
                        <span class="input-helper">Marca si la persona trabaja en la empresa</span>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">
                Cancelar
            </button>
            <button type="submit" form="cumpleanos-form" class="btn btn-primary">
                <span class="btn-icon">+</span>
                Agregar Cumpleaños
            </button>
        </div>
    </div>
</div>

<!-- Toast para mensajes de éxito -->
@if(session('success'))
    <div id="success-toast" class="toast toast-success">
        <div class="toast-content">
            <span class="toast-icon">✓</span>
            <span class="toast-message">{{ session('success') }}</span>
        </div>
        <button class="toast-close" onclick="closeToast()">×</button>
    </div>
@endif

<style>
    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Header */
    .header-section {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .header-content h1 {
        color: #2c3e50;
        font-size: 2rem;
        margin: 0;
        font-weight: 600;
        flex: 1;
    }

    /* Cards simplificados */
    .filters-card, .table-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }

    .filters-card h3, .table-card h3 {
        background: #f8f9fa;
        margin: 0;
        padding: 20px 25px;
        color: #495057;
        font-size: 1.2rem;
        font-weight: 500;
        border-bottom: 1px solid #dee2e6;
    }

    /* Filtros simplificados */
    .filters-content {
        padding: 25px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 25px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        margin-bottom: 8px;
        color: #495057;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.025em;
    }

    .filter-input {
        padding: 10px 14px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        font-size: 0.95rem;
        background: #fff;
        color: #495057;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .filter-input:focus {
        border-color: #007bff;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .filter-input::placeholder {
        color: #adb5bd;
    }

    /* Botones */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: #fff;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        color: #fff;
        box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #545b62 0%, #383d41 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    }

    .btn-icon {
        font-size: 1rem;
    }

    .btn-action {
        padding: 6px 8px;
        font-size: 0.8rem;
        min-width: 28px;
        min-height: 28px;
        text-align: center;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .action-icon {
        font-size: 0.9rem;
        line-height: 1;
    }

    /* Estados de botones */
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
        font-weight: 600;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: #fff;
        box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(23, 162, 184, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: #fff;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4);
    }

    /* Botón deshabilitado */
    .action-disabled {
        padding: 6px 8px;
        min-width: 28px;
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Tooltips mejorados */
    .btn-action[title]:hover::after,
    .action-disabled[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        white-space: nowrap;
        z-index: 1000;
        margin-bottom: 5px;
    }

    .btn-action[title]:hover::before,
    .action-disabled[title]:hover::before {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        margin-bottom: 1px;
    }

    /* Responsive para acciones */
    @media (max-width: 768px) {
        .col-acciones { width: 20%; }
        
        .actions-container {
            flex-direction: column;
            gap: 2px;
        }
        
        .btn-action {
            min-width: 24px;
            min-height: 24px;
            padding: 4px 6px;
        }
        
        .action-icon {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .actions-container {
            flex-direction: row;
            justify-content: center;
        }
        
        .btn-action {
            min-width: 20px;
            min-height: 20px;
            padding: 2px 4px;
        }
        
        .action-icon {
            font-size: 0.7rem;
        }
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(3px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.show {
        display: flex;
        opacity: 1;
    }

    .modal-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow: hidden;
        transform: scale(0.7);
        transition: transform 0.3s ease;
    }

    .modal-overlay.show .modal-container {
        transform: scale(1);
    }

    .modal-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-icon {
        font-size: 1.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.8rem;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: #f8f9fa;
        color: #495057;
    }

    .modal-body {
        padding: 30px 25px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .modal-footer {
        background: #f8f9fa;
        padding: 20px 25px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    .form-grid {
        display: grid;
        gap: 20px;
    }

    .input-group {
        display: flex;
        flex-direction: column;
    }

    .input-label {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #495057;
    }

    .required-asterisk {
        color: #dc3545;
        font-weight: 700;
    }

    .form-input {
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        font-size: 1rem;
        color: #495057;
        background: #fff;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .input-helper {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 4px;
        font-style: italic;
    }

    .checkbox-container {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 12px 0;
    }

    .checkbox-input {
        width: 20px;
        height: 20px;
        accent-color: #007bff;
        cursor: pointer;
    }

    .checkbox-text {
        font-size: 0.95rem;
        color: #495057;
        font-weight: 500;
    }

    .alert {
        padding: 16px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        border: 1px solid;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }

    .alert-icon {
        font-size: 1.2rem;
    }

    .alert-list {
        margin: 0;
        padding-left: 20px;
    }

    .alert-list li {
        margin-bottom: 4px;
    }

    /* Toast */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1100;
        min-width: 300px;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast-success {
        border-left: 4px solid #28a745;
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .toast-icon {
        background: #28a745;
        color: #fff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .toast-message {
        color: #495057;
        font-weight: 500;
    }

    .toast-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: #6c757d;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
    }

    /* Tabla */
    .table-header {
        background: #f8f9fa;
        padding: 20px 25px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header h3 {
        margin: 0;
        color: #495057;
        font-size: 1.2rem;
        font-weight: 500;
        background: none;
        padding: 0;
        border: none;
    }

    .record-count {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    .table-container {
        position: relative;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        table-layout: fixed;
    }

    .col-rut { width: 12%; }
    .col-nombre { width: 25%; }
    .col-fecha { width: 12%; }
    .col-edad { width: 8%; }
    .col-vinculado { width: 10%; }
    .col-email { width: 10%; }
    .col-proximo { width: 12%; }
    .col-acciones { width: 15%; }

    .data-table th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 12px 8px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table td {
        padding: 12px 8px;
        border-bottom: 1px solid #f1f3f4;
        color: #495057;
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .data-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .data-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .rut-text {
        font-family: 'Courier New', monospace;
        font-weight: 500;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .nombre-container {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .nombre-text {
        font-weight: 600;
        color: #2c3e50;
    }

    .fecha-text, .edad-text {
        color: #495057;
        font-weight: 500;
    }

    .action-disabled {
        color: #6c757d;
        font-style: italic;
    }

    /* Contador de días mejorado */
    .dias-contador {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 6px 8px;
        border-radius: 8px;
        min-width: 60px;
        font-weight: 600;
        text-align: center;
    }

    .dias-numero {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .dias-texto {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.8;
        margin-top: 2px;
    }

    .dias-emoji {
        font-size: 1.2rem;
        margin-left: 4px;
    }

    /* Estados de los contadores */
    .dias-contador.hoy {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
        border: 2px solid #ffc107;
        animation: pulse 1.5s ease-in-out infinite alternate;
    }

    .dias-contador.hoy .dias-numero {
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .dias-contador.proximos {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
        border: 2px solid #17a2b8;
    }

    .dias-contador.upcoming {
        background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
        color: #383d41;
        border: 2px solid #6c757d;
    }

    .dias-contador.normal {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        border: 1px solid #dee2e6;
    }

    @keyframes pulse {
        from {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }
        to {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge.vinculado-si {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .badge.vinculado-no {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }

    .badge.email-enviado {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .badge.email-pendiente {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .no-results {
        padding: 40px 20px;
        text-align: center;
        background: #f8f9fa;
    }

    .no-results-content {
        max-width: 300px;
        margin: 0 auto;
    }

    .no-results-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        display: block;
    }

    .no-results h4 {
        color: #495057;
        margin: 0 0 10px 0;
        font-weight: 600;
    }

    .no-results p {
        color: #6c757d;
        margin: 0;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
        font-style: italic;
        background-color: #f8f9fa;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .filter-row {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .filter-row .filter-group:last-child {
            grid-column: 1 / -1;
            justify-self: center;
        }
        
        .header-content {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }
        
        .filter-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .data-table {
            font-size: 0.8rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px 6px;
        }
        
        .col-nombre { width: 30%; }
        .col-rut { width: 15%; }
        .col-fecha { width: 15%; }
        .col-proximo { width: 15%; }
        .col-edad { width: 10%; }
        .col-vinculado { width: 15%; }
        
        .modal-container {
            width: 95%;
            margin: 20px;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .toast {
            right: 10px;
            left: 10px;
            min-width: auto;
        }
        
        .dias-contador {
            min-width: 50px;
            padding: 4px 6px;
        }
        
        .dias-numero {
            font-size: 0.9rem;
        }
        
        .dias-texto {
            font-size: 0.6rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables para filtros simplificados
    const searchNombre = document.getElementById('search-nombre');
    const searchRut = document.getElementById('search-rut');
    const clearButton = document.getElementById('clear-filters');
    const table = document.getElementById('cumpleanos-table');
    const tbody = table.querySelector('tbody');
    const noResults = document.getElementById('no-results');
    const totalRecords = document.getElementById('total-records');
    const emptyRow = document.getElementById('empty-row');

    // Variables para modal
    const modalOverlay = document.getElementById('modal-overlay');

    function filterTable() {
        const nombreTerm = searchNombre.value.toLowerCase().trim();
        const rutTerm = searchRut.value.toLowerCase().replace(/[.\-\s]/g, '').trim();
        
        const rows = tbody.querySelectorAll('.table-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const nombre = row.dataset.nombre;
            const rut = row.dataset.rut;
            const rutDisplay = row.dataset.rutDisplay;

            let showRow = true;

            if (nombreTerm && !nombre.includes(nombreTerm)) {
                showRow = false;
            }

            if (rutTerm && !rut.includes(rutTerm) && !rutDisplay.includes(rutTerm)) {
                showRow = false;
            }

            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0 && rows.length > 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }

        if (emptyRow && rows.length > 0) {
            emptyRow.style.display = 'none';
        }

        totalRecords.textContent = visibleCount;
    }

    function clearFilters() {
        searchNombre.value = '';
        searchRut.value = '';
        filterTable();
        searchNombre.focus();
    }

    let timeoutId = null;
    
    function debounceFilter() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(filterTable, 250);
    }

    searchNombre.addEventListener('input', debounceFilter);
    searchRut.addEventListener('input', debounceFilter);
    clearButton.addEventListener('click', clearFilters);

    const successToast = document.getElementById('success-toast');
    if (successToast) {
        setTimeout(() => {
            successToast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            closeToast();
        }, 5000);
    }

    @if ($errors->any())
        openModal();
    @endif

    filterTable();
});

function openModal() {
    const modal = document.getElementById('modal-overlay');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        document.getElementById('modal-rut').focus();
    }, 300);
}

function closeModal() {
    const modal = document.getElementById('modal-overlay');
    modal.classList.remove('show');
    document.body.style.overflow = '';
    
    document.getElementById('cumpleanos-form').reset();
}

function closeToast() {
    const toast = document.getElementById('success-toast');
    if (toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection
