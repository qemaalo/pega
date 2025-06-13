<!-- filepath: c:\wamp64\www\example-app2\resources\views\gantt\index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="gantt-container">
    <div class="gantt-header">
        <h1 class="gantt-title">Carta Gantt de Tareas</h1>
    </div>
    
    <div class="gantt-search-section">
        <form action="{{ route('compromops.index') }}" method="GET" class="gantt-search-form">
            <div class="search-container">
                <input type="text" name="search_op" placeholder="Buscar por OP" class="gantt-search-input" value="{{ request()->search_op ?? '' }}">
                <button type="submit" class="gantt-search-btn">🔎</button>
            </div>
        </form>
        
        <!-- Nueva navegación de meses -->
        <div class="gantt-month-navigation">
            <button id="prevMonth" class="gantt-nav-btn gantt-nav-btn-prev">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
                <span>Anterior</span>
            </button>
            
            <div class="gantt-month-display">
                <button id="currentMonthBtn" class="gantt-nav-btn-today">Hoy</button>
                <span id="currentMonthDisplay" class="gantt-current-month">{{ $dateString }}</span>
            </div>
            
            <button id="nextMonth" class="gantt-nav-btn gantt-nav-btn-next">
                <span>Siguiente</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <div class="gantt-chart-container">
        <!-- Barra de días del mes -->
        <div class="gantt-days-header">
            <div class="gantt-sidebar-header"></div>
            <div class="gantt-timeline-header">
      
                @for ($i = 1; $i <= $daysInMonth; $i++)
                    @php
                        $dayOfWeek = date('w', strtotime("$currentYear-$currentMonth-$i"));
                        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                    @endphp
                    <div class="gantt-day-column {{ $isWeekend ? 'weekend' : '' }}">
                        <div class="gantt-day-number">{{ $i }}</div>
                        <div class="gantt-day-name">{{ date('D', strtotime("$currentYear-$currentMonth-$i")) }}</div>
                    </div>
                @endfor
            </div>
        </div>
        
        <!-- Contenedor principal del Gantt -->
        <div class="gantt-body" id="ganttBody">
            <div class="gantt-sidebar">
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $task)
                        <div class="gantt-task-info" data-task-id="{{ $task->id }}">
                            <div class="gantt-task-title">{{ $task->np }} - {{ $task->linea }}</div>
                            <div class="gantt-task-subtitle">{{ $task->op }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="gantt-empty-sidebar">
                        Sin tareas para mostrar
                    </div>
                @endif
            </div>
            
            <div class="gantt-timeline" id="ganttTimeline">
                <!-- Grid de fondo -->
                <div class="gantt-grid">
                    @for ($i = 1; $i <= $daysInMonth; $i++)
                        @php
                            $dayOfWeek = date('w', strtotime("$currentYear-$currentMonth-$i"));
                            $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                        @endphp
                        <div class="gantt-grid-column {{ $isWeekend ? 'weekend' : '' }}"></div>
                    @endfor
                </div>
                
                <!-- Barras de tareas -->
                @if(isset($tasks) && $tasks->count() > 0)
                    @foreach($tasks as $index => $task)
                        @php
                            // Verificar que existan fechas
                            if (!$task->finicio || !$task->ftermino) continue;
                            
                            // Convertir las fechas de la tarea a objetos Carbon para mejor manipulación
                            $taskStartDate = \Carbon\Carbon::parse($task->finicio);
                            $taskEndDate = \Carbon\Carbon::parse($task->ftermino);
                            
                            // Obtener los límites del mes mostrado
                            $monthStart = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1);
                            $monthEnd = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, $daysInMonth);
                            
                            // Calcular las fechas efectivas dentro del mes visible
                            $effectiveStart = $taskStartDate->copy();
                            $effectiveEnd = $taskEndDate->copy();
                            
                            // Ajustar fechas si están fuera del mes visible
                            if ($effectiveStart->lt($monthStart)) $effectiveStart = $monthStart->copy();
                            if ($effectiveEnd->gt($monthEnd)) $effectiveEnd = $monthEnd->copy();
                            
                            // Convertir a día del mes (1-31)
                            $taskStart = $effectiveStart->day;
                            $taskEnd = $effectiveEnd->day;
                            
                            // Calcular posición y ancho como porcentaje del ancho total
                            $leftPosition = ($taskStart - 1) / $daysInMonth * 100;
                            $width = ($taskEnd - $taskStart + 1) / $daysInMonth * 100;
                            
                            // Asegurar ancho mínimo para visibilidad
                            if ($width < 2) $width = 2;
                            
                            // Calcular posición vertical
                            $topPosition = ($index * 46) + 10; // 10px de margen inicial
                            
                            // Generar etiquetas de fecha correctas (mostrando mes si difiere)
                            $startLabel = $taskStartDate->format('d') . ($taskStartDate->month != $currentMonth ? '/' . $taskStartDate->format('m') : '');
                            $endLabel = $taskEndDate->format('d') . ($taskEndDate->month != $currentMonth ? '/' . $taskEndDate->format('m') : '');
                        @endphp
                        
                        <div class="gantt-task-bar" 
                             data-task-id="{{ $task->id }}"
                             data-start-date="{{ $taskStartDate->format('Y-m-d') }}"
                             data-end-date="{{ $taskEndDate->format('Y-m-d') }}"
                             data-activo="{{ $task->activo }}"
                             style="left: {{ $leftPosition }}%; width: {{ $width }}%; top: {{ $topPosition }}px;">
                            <div class="gantt-task-content">
                                <span class="gantt-task-dates">{{ $startLabel }} - {{ $endLabel }}</span>
                            </div>
                            @if($task->activo)
                                <div class="gantt-task-resizer gantt-task-resizer-left"></div>
                                <div class="gantt-task-resizer gantt-task-resizer-right"></div>
                            @endif
                        </div>
                    @endforeach
                @endif
                
                <!-- Área para crear nuevas tareas -->
                <div id="newTaskArea" class="gantt-new-task-area"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Contenedor principal */
    .gantt-container {
        max-width: 100%;
        overflow-x: hidden;
        margin: 40px auto;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.03);
        padding: 35px 30px;
    }
    
    /* Encabezado */
    .gantt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .gantt-title {
        font-size: 1.8rem;
        color: #333;
        margin: 0;
        font-weight: 500;
        letter-spacing: -0.5px;
    }
    
    .gantt-btn {
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
    
    .gantt-btn:hover {
        background: #222;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
    
    .gantt-btn-secondary {
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
        cursor: pointer;
    }
    
    .gantt-btn-secondary:hover {
        background: #e8e8e8;
    }
    
    /* Sección de búsqueda */
    .gantt-search-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .gantt-search-form {
        display: flex;
        align-items: center;
        align-items: center;
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
    
    .gantt-search-input {
        padding: 8px 12px;
        border: none;
        font-size: 0.85rem;
        width: 180px;
        background: transparent;
    }
    
    .gantt-search-input:focus {
        outline: none;
    }
    
    .gantt-search-btn {
        padding: 8px 10px;
        background: transparent;
        border: none;
        color: #777;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .gantt-search-btn:hover {
        background: #f5f5f5;
        color: #333;
    }
    
    /* Navegación del mes - versión sofisticada */
    .gantt-month-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid #eaeaea;
        margin-left: auto;
        width: 100%;
        max-width: 520px;
    }

    .gantt-nav-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    /* Botón mes anterior */
    .gantt-nav-btn-prev {
        background: #ff9800;
        color: white;
    }

    .gantt-nav-btn-prev:hover {
        background: #e68a00;
        transform: translateX(-2px);
    }

    /* Botón mes siguiente */
    .gantt-nav-btn-next {
        background: #4caf50;
        color: white;
    }

    .gantt-nav-btn-next:hover {
        background: #3d8b40;
        transform: translateX(2px);
    }

    /* Visualización del mes actual */
    .gantt-month-display {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        position: relative;
    }

    .gantt-month-display:before {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 15%;
        right: 15%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #4a6cf7, transparent);
        border-radius: 2px;
    }

    .gantt-current-month {
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
        text-align: center;
    }
    
    /* Botón mes actual/hoy */
    .gantt-nav-btn-today {
        background: #4a6cf7;
        color: white;
        font-size: 0.75rem;
        padding: 4px 14px;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 4px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .gantt-nav-btn-today:hover {
        background: #3955d4;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(74, 108, 247, 0.3);
    }
    
    /* Contenedor del Gantt */
    .gantt-chart-container {
        width: 100%;
        border: 1px solid #eaeaea;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    /* Cabecera de días */
    .gantt-days-header {
        display: flex;
        background: #f7f7f7;
        border-bottom: 1px solid #eaeaea;
    }
    
    .gantt-sidebar-header {
        width: 160px; /* Reducido de 220px a 160px */
        min-width: 160px;
        border-right: 1px solid #eaeaea;
    }
    
    .gantt-timeline-header {
        display: flex;
        flex: 1;
        overflow-x: hidden;
        width: calc(100% - 160px); /* Ajustado para compensar la nueva anchura */
    }
    
    .gantt-day-column {
        flex: 1 0 auto;
        width: calc(100% / {{ $daysInMonth }});
        min-width: unset;
    }
    
    .gantt-day-column.weekend {
        background: #f9f9f9;
    }
    
    .gantt-day-column:last-child {
        border-right: none;
    }
    
    .gantt-day-number {
        font-weight: 500;
        font-size: 0.9rem;
        color: #333;
    }
    
    .gantt-day-name {
        font-size: 0.75rem;
        color: #777;
        margin-top: 2px;
    }
    
    /* Cuerpo del Gantt */
    .gantt-body {
        display: flex;
        height: 500px;
        overflow-y: auto;
        position: relative;
    }
    
    /* Sidebar con información de tareas */
    .gantt-sidebar {
        width: 160px; /* Reducido de 220px a 160px */
        min-width: 160px;
        background: #fbfbfb;
        border-right: 1px solid #eaeaea;
        overflow-y: auto;
    }
    
    .gantt-task-info {
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.2s;
        height: 50px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .gantt-task-info:hover {
        background-color: #f5f5f5;
    }
    
    .gantt-task-title {
        font-weight: 500;
        font-size: 0.85rem;
        color: #333;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }
    
    .gantt-task-subtitle {
        font-size: 0.75rem;
        color: #777;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }
    
    .gantt-empty-sidebar {
        padding: 20px 15px;
        color: #999;
        font-style: italic;
        text-align: center;
    }
    
    /* Timeline con barras de tareas */
    .gantt-timeline {
        flex: 1;
        position: relative;
        overflow-x: hidden;
        width: calc(100% - 160px); /* Ajustado para compensar la nueva anchura */
    }
    
    /* Grid de fondo */
    .gantt-grid {
        display: flex;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    
    .gantt-grid-column {
        flex: 1 0 auto;
        width: calc(100% / {{ $daysInMonth }});
        min-width: unset;
        height: 100%;
        border-right: 1px solid #f0f0f0;
    }
    
    .gantt-grid-column.weekend {
        background: #f9f9f9;
    }
    
    /* Barras de tareas */
    .gantt-task-bar {
        position: absolute;
        height: 28px; /* Ligeramente menor para más espacio entre barras */
        background-color: #4a6cf7;
        border-radius: 4px;
        z-index: 10;
        cursor: move;
        color: white;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        /* Eliminar margin-top ya que usamos top absoluto */
    }
    
    .gantt-task-content {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        padding: 0 10px;
    }
    
    .gantt-task-dates {
        font-size: 0.75rem;
        white-space: nowrap;
    }
    
    /* Redimensionadores de tareas */
    .gantt-task-resizer {
        position: absolute;
        top: 0;
        width: 6px;
        height: 100%;
        cursor: col-resize;
        z-index: 11;
    }
    
    .gantt-task-resizer-left {
        left: 0;
    }
    
    .gantt-task-resizer-right {
        right: 0;
    }
    
    /* Área para crear nuevas tareas */
    .gantt-new-task-area {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9;
    }
    
    .gantt-selection-box {
        position: absolute;
        background-color: rgba(74, 108, 247, 0.3);
        border: 2px solid #4a6cf7;
        z-index: 10;
        pointer-events: none;
    }
    
    /* Modal */
    .gantt-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .gantt-modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: 80%;
        max-width: 600px;
        position: relative;
    }
    
    .gantt-modal-close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 1.5rem;
        color: #aaa;
        cursor: pointer;
    }
    
    .gantt-modal-close:hover {
        color: #555;
    }
    
    /* Formulario */
    .gantt-form-group {
        margin-bottom: 15px;
        flex: 1;
    }
    
    .gantt-form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .gantt-form-label {
        display: block;
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 6px;
        font-weight: 500;
    }
    
    .gantt-form-input, .gantt-form-textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.95rem;
        color: #333;
        transition: border-color 0.2s;
        background: #fff;
    }
    
    .gantt-form-input:focus, .gantt-form-textarea:focus {
        border-color: #aaa;
        outline: none;
    }
    
    .gantt-form-textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .gantt-form-footer {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .required {
        color: #d32f2f;
    }
    
    /* Notificaciones */
    .gantt-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: #4a6cf7;
        color: white;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1100;
        transform: translateY(-20px);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .gantt-notification-success {
        background: #4caf50;
    }

    .gantt-notification-error {
        background: #f44336;
    }

    /* boton fecha actual*/
    /* Estilos para el contenedor del mes y el botón "Mes actual" */
    .gantt-month-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .gantt-nav-btn-today {
        background: #4a6cf7;
        color: white;
        font-size: 0.8rem;
        padding: 4px 10px;
        margin-bottom: 2px;
    }

    .gantt-nav-btn-today:hover {
        background: #3955d4;
    }

    /* Estilos para el botón de confirmación */
    .gantt-confirm-action {
        position: absolute;
        right: -35px;
        top: 0;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .gantt-confirm-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #4caf50;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        border: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .gantt-confirm-btn:hover {
        background-color: #3d8b40;
        transform: scale(1.1);
    }

    .gantt-cancel-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #f44336;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        border: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .gantt-cancel-btn:hover {
        background-color: #d32f2f;
        transform: scale(1.1);
    }

    /* Añade estos estilos junto a los otros botones */
    .gantt-comment-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #ff9800;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        border: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .gantt-comment-btn:hover {
        background-color: #e68a00;
        transform: scale(1.1);
    }

    /* Estilos para el modal de comentarios */
    .gantt-comment-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }

    .gantt-comment-modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        width: 80%;
        max-width: 500px;
    }

    .gantt-comment-textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 100px;
        margin: 10px 0;
        font-family: inherit;
    }

    .gantt-comment-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 15px;
    }

    /* Estilo para barras inactivas */
    .gantt-task-bar[data-activo="0"] {
        background-color: #a0a0a0 !important; /* Color gris */
        opacity: 0.7;
        cursor: not-allowed !important; /* Cursor que indica que no es interactivo */
    }

    /* Ocultar los redimensionadores en tareas inactivas */
    .gantt-task-bar[data-activo="0"] .gantt-task-resizer {
        display: none !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const currentMonthDisplay = document.getElementById('currentMonthDisplay');
    const ganttTimeline = document.getElementById('ganttTimeline');
    
    // Variables para seguimiento
    let currentMonth = new Date({{ $currentYear }}, {{ $currentMonth - 1 }});
    let currentYear = {{ $currentYear }};
    let currentMonthIndex = {{ $currentMonth - 1 }}; // JS usa 0-11 para meses
    let daysInMonth = {{ $daysInMonth }};
    
    // Declarar las variables faltantes
    let draggingTask = null;
    let resizing = null;
    let initialX = 0;
    let initialLeft = 0;
    let initialWidth = 0;
    
    // Mantener solo la función de formato de fecha
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Eventos para las tareas existentes
    document.querySelectorAll('.gantt-task-bar').forEach(setupTaskBarEvents);
    
    function setupTaskBarEvents(taskBar) {
        // Verificar si la tarea está inactiva
        if (taskBar.getAttribute('data-activo') === "0") {
            return; // No configurar eventos para tareas inactivas
        }
        
        // Resto del código sin cambios
        taskBar.addEventListener('mousedown', function(e) {
            // No permitir mover si hay confirmación pendiente
            if (taskBar.querySelector('.gantt-confirm-action')) {
                return;
            }
            
            // Detener cualquier arrastre anterior (por si acaso)
            draggingTask = null;
            resizing = null;
            
            if (e.target.classList.contains('gantt-task-resizer')) {
                // Iniciar redimensionamiento
                resizing = {
                    task: this,
                    isLeft: e.target.classList.contains('gantt-task-resizer-left')
                };
                initialX = e.clientX;
                initialLeft = parseFloat(this.style.left);
                initialWidth = parseFloat(this.style.width);
            } else {
                // Iniciar arrastre
                draggingTask = this;
                initialX = e.clientX;
                initialLeft = parseFloat(this.style.left);
            }
            e.preventDefault();
        });
    }
    
    // Simplificar el evento mousemove para eliminar la creación
    document.addEventListener('mousemove', function(e) {
        // Si no hay nada siendo arrastrado, salir
        if (!draggingTask && !resizing) return;
        
        // No mover nada si hay alguna confirmación pendiente
        const pendingConfirmations = document.querySelectorAll('.gantt-confirm-action');
        if (pendingConfirmations.length > 0) {
            draggingTask = null;
            resizing = null;
            return;
        }
        
        if (draggingTask) {
            // Mover tarea existente
            const dx = e.clientX - initialX;
            const timelineWidth = ganttTimeline.clientWidth;
            const percentageMoved = (dx / timelineWidth) * 100;
            let newLeft = initialLeft + percentageMoved;
            
            // Limitar al rango válido
            newLeft = Math.max(0, Math.min(100 - parseFloat(draggingTask.style.width), newLeft));
            
            draggingTask.style.left = newLeft + '%';
            
            // Actualizar texto de fechas
            updateTaskDates(draggingTask);
        } else if (resizing) {
            // Redimensionar tarea existente
            const dx = e.clientX - initialX;
            const timelineWidth = ganttTimeline.clientWidth;
            const percentageMoved = (dx / timelineWidth) * 100;
            
            if (resizing.isLeft) {
                // Redimensionar desde la izquierda
                let newLeft = initialLeft + percentageMoved;
                let newWidth = initialWidth - percentageMoved;
                
                // Limitar al rango válido
                newLeft = Math.max(0, newLeft);
                newWidth = Math.max(100/daysInMonth, newWidth); // Al menos 1 día
                
                if (newLeft + newWidth <= 100) {
                    resizing.task.style.left = newLeft + '%';
                    resizing.task.style.width = newWidth + '%';
                    updateTaskDates(resizing.task);
                }
            } else {
                // Redimensionar desde la derecha
                let newWidth = initialWidth + percentageMoved;
                
                // Limitar al rango válido
                newWidth = Math.max(100/daysInMonth, Math.min(100 - initialLeft, newWidth));
                
                resizing.task.style.width = newWidth + '%';
                updateTaskDates(resizing.task);
            }
        }
    });
    
    // Evento mouseup para finalizar arrastre/redimensionamiento
    document.addEventListener('mouseup', function(e) {
        // Si estábamos arrastrando una tarea
        if (draggingTask) {
            // Mostrar botones de confirmación
            showConfirmationButtons(draggingTask);
            // ¡IMPORTANTE! Reiniciar la variable draggingTask
            draggingTask = null;
        }
        
        // Si estábamos redimensionando una tarea
        if (resizing) {
            // Mostrar botones de confirmación
            showConfirmationButtons(resizing.task);
            // ¡IMPORTANTE! Reiniciar la variable resizing
            resizing = null;
        }
    });
    
    // Mantener las funciones restantes sin cambios
    function updateTaskDates(taskBar) {
        const left = parseFloat(taskBar.style.left);
        const width = parseFloat(taskBar.style.width);
        
        // Calcular fechas basadas en posición y ancho
        const startDay = Math.max(1, Math.ceil(left / 100 * daysInMonth));
        const endDay = Math.min(daysInMonth, Math.floor((left + width) / 100 * daysInMonth));
        
        // Crear objetos Date
        const startDate = new Date(currentYear, currentMonthIndex, startDay);
        const endDate = new Date(currentYear, currentMonthIndex, endDay);
        
        // Formatear fechas para mostrar
        const startFormatted = `${startDay}/${currentMonthIndex + 1}`;
        const endFormatted = `${endDay}/${currentMonthIndex + 1}`;
        
        // Actualizar texto en la barra
        const datesSpan = taskBar.querySelector('.gantt-task-dates');
        if (datesSpan) {
            datesSpan.textContent = `${startFormatted} - ${endFormatted}`;
        }
        
        // Actualizar atributos de fecha
        taskBar.setAttribute('data-start-date', formatDate(startDate));
        taskBar.setAttribute('data-end-date', formatDate(endDate));
    }
    
    // Actualizar las fechas en la base de datos
    function updateTaskDatesInDB(taskBar) {
        const taskId = taskBar.getAttribute('data-task-id');
        const startDate = taskBar.getAttribute('data-start-date');
        const endDate = taskBar.getAttribute('data-end-date');
        
        // Mostrar datos que se envían (para depuración)
        console.log('Enviando actualización para tarea:', {
            id: taskId,
            finicio: startDate,
            ftermino: endDate
        });
        
        // Crear FormData
        const formData = new FormData();
        formData.append('finicio', startDate);
        formData.append('ftermino', endDate);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // URL específica para la actualización AJAX - modificada para funcionar tanto en desarrollo como producción
        let baseUrl = window.location.origin;
        // Si estamos en localhost, no incluir el path adicional
        const url = baseUrl.includes('localhost') 
            ? `${baseUrl}/compromops/${taskId}/ajax-update`
            : `${baseUrl}/example-app2/public/compromops/${taskId}/ajax-update`;
        
        console.log('URL de actualización:', url);
        
        // Usar fetch con la URL correcta
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Respuesta de error:', text);
                    throw new Error(`Error ${response.status}: ${text.substring(0, 100)}...`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Tarea actualizada:', data);
            showNotification('Tarea actualizada correctamente');
        })
        .catch(error => {
            console.error('Error al actualizar:', error);
            showNotification('Error al actualizar la tarea: ' + error.message, true);
        });
    }

    // Añadir esta función si no existe
    function showNotification(message, isError = false) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `gantt-notification ${isError ? 'gantt-notification-error' : 'gantt-notification-success'}`;
        notification.textContent = message;
        
        // Añadir al DOM
        document.body.appendChild(notification);
        
        // Mostrar con animación
        setTimeout(() => {
            notification.style.transform = 'translateY(0)';
            notification.style.opacity = '1';
        }, 10);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.style.transform = 'translateY(-20px)';
            notification.style.opacity = '0';
            
            // Eliminar del DOM después de la animación
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Navegación entre meses
    prevMonthBtn.addEventListener('click', function() {
        navigateMonth(-1);
    });
    
    nextMonthBtn.addEventListener('click', function() {
        navigateMonth(1);
    });
    
    function navigateMonth(direction) {
        // Actualizar mes actual
        currentMonthIndex += direction;
        
        if (currentMonthIndex < 0) {
            currentMonthIndex = 11;
            currentYear--;
        } else if (currentMonthIndex > 11) {
            currentMonthIndex = 0;
            currentYear++;
        }
        
        currentMonth = new Date(currentYear, currentMonthIndex, 1);
        
        // Actualizar texto del mes
        currentMonthDisplay.textContent = formatMonthYear(currentMonth);
        
        // Recargar la página con el nuevo mes
        window.location.href = `{{ route('compromops.index') }}?month=${currentMonthIndex + 1}&year=${currentYear}`;
    }
    
    function formatMonthYear(date) {
        const options = { month: 'long', year: 'numeric' };
        return date.toLocaleDateString('es-ES', options);
    }
    // Añadir esto después de los otros eventos de navegación
    const currentMonthBtn = document.getElementById('currentMonthBtn');

    currentMonthBtn.addEventListener('click', function() {
        // Obtener la fecha actual
        const today = new Date();
        const todayMonth = today.getMonth() + 1; // getMonth() devuelve 0-11
        const todayYear = today.getFullYear();
        
        // Redirigir a la página con el mes actual
        window.location.href = `{{ route('compromops.index') }}?month=${todayMonth}&year=${todayYear}`;
    });
    
    // Función para mostrar botones de confirmación
    function showConfirmationButtons(taskBar) {
        // Guardar datos originales para poder restaurar si se cancela
        const originalStartDate = taskBar.getAttribute('data-start-date');
        const originalEndDate = taskBar.getAttribute('data-end-date');
        const originalLeft = taskBar.style.left;
        const originalWidth = taskBar.style.width;
        
        // Eliminar botones anteriores si existen
        const existingConfirm = taskBar.querySelector('.gantt-confirm-action');
        if (existingConfirm) {
            existingConfirm.remove();
        }
        
        // Crear contenedor para botones
        const confirmContainer = document.createElement('div');
        confirmContainer.className = 'gantt-confirm-action';
        
        // Botón de confirmar
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'gantt-confirm-btn';
        confirmBtn.innerHTML = '✓';
        confirmBtn.title = 'Confirmar cambio';
        
        // Botón de comentario
        const commentBtn = document.createElement('button');
        commentBtn.className = 'gantt-comment-btn';
        commentBtn.innerHTML = '💬';
        commentBtn.title = 'Añadir comentario';
        
        // Botón de cancelar
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'gantt-cancel-btn';
        cancelBtn.innerHTML = '✕';
        cancelBtn.title = 'Cancelar cambio';
        
        // Agregar evento al botón de confirmar
        confirmBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que el clic propague
            
            // Actualizar en la base de datos
            updateTaskDatesInDB(taskBar);
            
            // Eliminar botones
            confirmContainer.remove();
        });
        
        // Agregar evento al botón de comentario
        commentBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que el clic propague
            
            // Mostrar modal de comentarios
            showCommentModal(taskBar);
        });
        
        // Agregar evento al botón de cancelar
        cancelBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que el clic propague
            
            // Restaurar posición y tamaño originales
            taskBar.style.left = originalLeft;
            taskBar.style.width = originalWidth;
            
            // Restaurar fechas originales
            taskBar.setAttribute('data-start-date', originalStartDate);
            taskBar.setAttribute('data-end-date', originalEndDate);
            
            // Actualizar el texto en la barra
            const startDate = new Date(originalStartDate);
            const endDate = new Date(originalEndDate);
            const datesSpan = taskBar.querySelector('.gantt-task-dates');
            if (datesSpan) {
                datesSpan.textContent = `${startDate.getDate()}/${startDate.getMonth() + 1} - ${endDate.getDate()}/${endDate.getMonth() + 1}`;
            }
            
            // Eliminar botones
            confirmContainer.remove();
            
            // Mostrar notificación
            showNotification('Cambios cancelados');
        });
        
        // Agregar botones al contenedor
        confirmContainer.appendChild(confirmBtn);
        confirmContainer.appendChild(commentBtn);
        confirmContainer.appendChild(cancelBtn);
        
        // Agregar contenedor a la barra
        taskBar.appendChild(confirmContainer);
    }

    // Función para mostrar el modal de comentarios
    function showCommentModal(taskBar) {
        const taskId = taskBar.getAttribute('data-task-id');
        
        // Crear el modal si no existe
        let commentModal = document.getElementById('ganttCommentModal');
        if (!commentModal) {
            commentModal = document.createElement('div');
            commentModal.id = 'ganttCommentModal';
            commentModal.className = 'gantt-comment-modal';
            
            // Contenido del modal
            commentModal.innerHTML = `
                <div class="gantt-comment-modal-content">
                    <h3>Añadir comentario</h3>
                    <textarea id="taskComment" class="gantt-comment-textarea" placeholder="Escribe tu comentario aquí..."></textarea>
                    <div class="gantt-comment-actions">
                        <button id="cancelComment" class="gantt-btn-secondary">Cancelar</button>
                        <button id="saveComment" class="gantt-btn">Guardar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(commentModal);
            
            // Evento para cerrar el modal con el botón Cancelar
            document.getElementById('cancelComment').addEventListener('click', function() {
                commentModal.style.display = 'none';
            });
            
            // Cerrar el modal al hacer clic fuera del contenido
            commentModal.addEventListener('click', function(e) {
                if (e.target === commentModal) {
                    commentModal.style.display = 'none';
                }
            });
        }
        
        // Mostrar el modal
        commentModal.style.display = 'block';
        
        // Evento para guardar comentario
        document.getElementById('saveComment').onclick = function() {
            const commentText = document.getElementById('taskComment').value.trim();
            
            if (commentText) {
                // Guardar el comentario
                saveTaskComment(taskId, commentText, taskBar);
                
                // Cerrar el modal
                commentModal.style.display = 'none';
            } else {
                showNotification('Por favor, escribe un comentario', true);
            }
        };
    }

    // Función para guardar el comentario
    function saveTaskComment(taskId, comment, taskBar) {
        // URL para guardar el comentario
        let baseUrl = window.location.origin;
        const url = baseUrl.includes('localhost') 
            ? `${baseUrl}/compromops/${taskId}/comment`
            : `${baseUrl}/example-app2/public/compromops/${taskId}/comment`;
        
        // Datos del comentario (sin incluir finicio y ftermino)
        const formData = new FormData();
        formData.append('comment', comment);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        // Eliminadas las líneas de finicio y ftermino
    
        // Enviar la solicitud
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Comentario guardado correctamente');
                
                // También actualizar la tarea
                updateTaskDatesInDB(taskBar);
                
                // Eliminar botones de confirmación
                const confirmContainer = taskBar.querySelector('.gantt-confirm-action');
                if (confirmContainer) {
                    confirmContainer.remove();
                }
            } else {
                showNotification('Error: ' + data.message, true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al guardar comentario', true);
        });
    }
});
</script>
@endsection
