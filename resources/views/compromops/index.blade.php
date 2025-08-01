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
                <input type="text" name="search_op" placeholder="Buscar por OP..." class="gantt-search-input" value="{{ request()->search_op ?? '' }}">
                <button type="submit" class="gantt-search-btn">Buscar</button>
            </div>
        </form>
        
        <!-- Nueva navegación de semestres -->
        <div class="gantt-month-navigation">
            <button id="prevSemester" class="gantt-nav-btn gantt-nav-btn-prev">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
                <span>Semestre Anterior</span>
            </button>
            
            <div class="gantt-month-display">
                <span id="currentSemesterDisplay" class="gantt-current-month">{{ $dateString }}</span>
                <small style="opacity: 0.9; font-size: 0.9rem;">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</small>
            </div>
            
            <button id="nextSemester" class="gantt-nav-btn gantt-nav-btn-next">
                <span>Semestre Siguiente</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"></path>
                </svg>
            </button>
            
            <button id="currentSemesterBtn" class="gantt-nav-btn-today">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Semestre Actual
            </button>
        </div>
    </div>
    
    <div class="gantt-chart-container" style="display: flex; width: 100%; height: 100%;">
        <!-- Sidebar fija a la izquierda con header sticky -->
        <div style="display: flex; flex-direction: column; min-width: 300px; max-width: 300px; height: 100%;">
            <div class="gantt-sidebar-header" style="position: sticky; top: 0; z-index: 2; background: #fff;">Maquinarias</div>
            <div class="gantt-sidebar" style="flex: 1 1 auto; overflow-y: auto; min-width: 300px; max-width: 300px; background: #fff;">
                @if(isset($centros) && $centros->count() > 0)
                    @foreach($centros as $centro)
                        <div class="gantt-centro-group" data-centro-id="{{ $centro->id }}">
                            <div class="gantt-centro-header" onclick="toggleCentroGroup({{ $centro->id }})">
                                <div class="gantt-centro-info">
                                    <span class="gantt-centro-toggle">▼</span>
                                    <h4>{{ $centro->descripcion }}</h4>
                                </div>
                                <span class="gantt-centro-count">({{ $centro->maquinarias->count() }})</span>
                            </div>
                            <div class="gantt-centro-maquinarias" id="centro-maquinarias-{{ $centro->id }}">
                                @foreach($centro->maquinarias as $maquinaria)
                                    <div class="gantt-maquinaria-row" data-maquinaria-id="{{ $maquinaria->id }}">
                                        <div class="gantt-maquinaria-name">{{ $maquinaria->nombre }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="gantt-empty-sidebar">
                        Sin maquinarias configuradas
                    </div>
                @endif
            </div>
        </div>
        <!-- Grid y timeline scrollable a la derecha -->
        <div class="gantt-scroll-x" style="overflow-x: auto; width: calc(100% - 300px); height: 100%;">
            <div style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px;">
                <div class="gantt-timeline-header" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; display: flex; overflow-x: hidden !important;">
                    @for ($i = 0; $i < $totalDays; $i++)
                        @php
                            $currentDate = $startDate->copy()->addDays($i);
                            $dayOfWeek = $currentDate->dayOfWeek;
                            $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                            $isFirstOfMonth = $currentDate->day == 1;
                        @endphp
                        <div class="gantt-day-column {{ $isWeekend ? 'weekend' : '' }} {{ $isFirstOfMonth ? 'first-of-month' : '' }}" style="width: 50px; min-width: 50px;">
                            <div class="gantt-day-number">{{ $currentDate->day }}</div>
                            <div class="gantt-day-name">
                                @if($isFirstOfMonth)
                                    {{ $currentDate->format('M') }}
                                @else
                                    {{ $currentDate->format('D') }}
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="gantt-body" id="ganttBody" style="display: flex;">
                    <div class="gantt-timeline" id="ganttTimeline" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; overflow-x: hidden !important;">
                        <div class="gantt-scroll-content" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; position: relative; height: auto; overflow-x: hidden !important;">
                            <div class="gantt-grid" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; position: relative; overflow-x: hidden !important;">
                                @if(isset($centros))
                                    @php $maquinariaPositions = []; $currentPosition = 0; @endphp
                                    @foreach($centros as $centro)
                                        @php $currentPosition += 54; @endphp
                                        @foreach($centro->maquinarias as $maquinaria)
                                            @php $maquinariaPositions[$maquinaria->id] = $currentPosition + 15; @endphp
                                            <div class="gantt-grid-row" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; height: 60px; position: relative; left: 0; display: flex;" data-maquinaria-id="{{ $maquinaria->id }}">
                                                @for ($i = 0; $i < $totalDays; $i++)
                                                    @php
                                                        $currentDate = $startDate->copy()->addDays($i);
                                                        $dayOfWeek = $currentDate->dayOfWeek;
                                                        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
                                                        $isFirstOfMonth = $currentDate->day == 1;
                                                    @endphp
                                                    <div class="gantt-grid-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isFirstOfMonth ? 'first-of-month' : '' }}" style="width: 50px; min-width: 50px; height: 100%;"></div>
                                                @endfor
                                            </div>
                                            @php $currentPosition += 60; @endphp
                                        @endforeach
                                    @endforeach
                                @endif
                            </div>
                            <!-- Barras de tareas -->
                            @if(isset($tasks) && $tasks->count() > 0)
                                @foreach($tasks as $task)
                                    @php
                                        if (!$task->finicio || !$task->ftermino) continue;
                                        $taskStartDate = \Carbon\Carbon::parse($task->finicio);
                                        $taskEndDate = \Carbon\Carbon::parse($task->ftermino);
                                        $effectiveStart = $taskStartDate->copy();
                                        $effectiveEnd = $taskEndDate->copy();
                                        if ($effectiveStart->lt($startDate)) $effectiveStart = $startDate->copy();
                                        if ($effectiveEnd->gt($endDate)) $effectiveEnd = $endDate->copy();
                                        $taskStartDay = $startDate->diffInDays($effectiveStart);
                                        $taskEndDay = $startDate->diffInDays($effectiveEnd);
                                        $leftPosition = ($taskStartDay) * 50;
                                        $width = ($taskEndDay - $taskStartDay + 1) * 50;
                                        if ($width < 25) $width = 25;
                                        $rowHeight = 60;
                                        $barHeight = 45;
                                        $topPosition = 0;
                                        if ($task->maquinaria_id && isset($maquinariaPositions[$task->maquinaria_id])) {
                                            // Centrar la barra en la fila: posición de la fila + (rowHeight / 2) - (barHeight / 2)
                                            $topPosition = $maquinariaPositions[$task->maquinaria_id] + ($rowHeight / 2) - ($barHeight / 2);
                                        }
                                        $taskContent = "OP {$task->op}-{$task->linea}";
                                        $barColor = '#4a6cf7';
                                        if ($task->maquinaria && $task->maquinaria->centro) {
                                            switch($task->maquinaria->centro->descripcion) {
                                                case 'PRENSA': $barColor = '#ff6b6b'; break;
                                                case 'REVESTIMIENTO': $barColor = '#4ecdc4'; break;
                                                case 'POLIURETANO': $barColor = '#45b7d1'; break;
                                                case 'TRAFILA': $barColor = '#96ceb4'; break;
                                                case 'ANILLOS': $barColor = '#feca57'; break;
                                            }
                                        }
                                    @endphp
                                    <div class="gantt-task-bar" 
                                         data-task-id="{{ $task->id }}"
                                         data-start-date="{{ $taskStartDate->format('Y-m-d') }}"
                                         data-end-date="{{ $taskEndDate->format('Y-m-d') }}"
                                         data-maquinaria-id="{{ $task->maquinaria_id ?? '' }}"
                                         data-activo="{{ $task->activo }}"
                                         style="left: {{ $leftPosition }}px; width: {{ $width }}px; top: {{ $topPosition }}px; background-color: {{ $barColor }}; position: absolute; height: 45px;"
                                         title="OP {{ $task->op }}-{{ $task->linea }} | {{ $task->maquinaria->nombre ?? 'Sin maquinaria' }} | {{ $taskStartDate->format('d/m') }} - {{ $taskEndDate->format('d/m') }}">
                                        <div class="gantt-task-content">
                                            <span class="gantt-task-label">{{ $taskContent }}</span>
                                        </div>
                                        @if($task->activo)
                                            <div class="gantt-task-resizer gantt-task-resizer-left"></div>
                                            <div class="gantt-task-resizer gantt-task-resizer-right"></div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <!-- Área para crear nuevas tareas -->
                        <div id="newTaskArea" class="gantt-new-task-area"></div>
                    </div>

<style>
    /* Contenedor principal - Maximizado para 1920x1080 */
    .gantt-container {
        width: 100vw;
        max-width: none;
        margin: 0;
        background: #ffffff;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
        height: calc(100vh - 60px); /* Altura completa menos header */
        display: flex;
        flex-direction: column;
        /* Quitar overflow: hidden para permitir scroll interno */
    }

    /* Forzar scroll horizontal en el contenedor de la grilla */
    .gantt-scroll-x {
        overflow-x: auto;
        width: 100%;
        min-height: 400px;
        height: auto;
        position: relative;
        background: #fff;
        border-bottom: 1px solid #eee;
        display: block;
    }
    .gantt-scroll-content {
        min-width: 100%;
        width: {{ $totalDays * 50 }}px;
        position: relative;
        min-height: 400px;
        height: auto;
        display: block;
        overflow: hidden !important;
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
        overscroll-behavior: none !important;
    }
    .gantt-scroll-content::-webkit-scrollbar {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
        background: transparent !important;
    }
    .gantt-grid {
        min-width: {{ $totalDays * 50 }}px;
        width: {{ $totalDays * 50 }}px;
        position: relative;
        height: auto;
        display: block;
        overflow-x: hidden !important;
        overflow: -moz-scrollbars-none;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .gantt-grid::-webkit-scrollbar {
        display: none !important;
    }
    
    /* Encabezado */
    .gantt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0;
        padding: 10px 20px;
        border-bottom: 2px solid #e0e0e0;
        background: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 300;
        flex-shrink: 0;
    }
    
    .gantt-title {
        font-size: 2.8rem;
        color: #2c3e50;
        margin: 0;
        font-weight: 700;
        letter-spacing: -0.8px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .gantt-btn {
        display: inline-flex;
        align-items: center;
        background: #3a3a3a;
        color: #fff;
        padding: 10px 20px; /* Aumentado */
        border-radius: 6px;
        text-decoration: none;
        font-size: 1rem; /* Aumentado */
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
        padding: 10px 20px; /* Aumentado */
        border-radius: 6px;
        text-decoration: none;
        font-size: 1rem; /* Aumentado */
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
        margin-bottom: 0;
        padding: 15px 20px;
        background: #fff;
        border-bottom: 2px solid #e0e0e0;
        position: sticky;
        top: 82px;
        z-index: 290;
        flex-shrink: 0;
    }
    
    .gantt-search-form {
        display: flex;
        align-items: center;
    }
    
    .search-container {
        display: flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
        background: #fff;
        transition: all 0.3s ease;
    }
    
    .search-container:focus-within {
        border-color: #3498db;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
    }
    
    .gantt-search-input {
        padding: 15px 20px;
        border: none;
        font-size: 1.1rem;
        width: 300px;
        background: transparent;
        color: #2c3e50;
        font-weight: 500;
    }
    
    .gantt-search-input:focus {
        outline: none;
    }
    
    .gantt-search-input::placeholder {
        color: #7f8c8d;
        font-weight: 400;
    }
    
    .gantt-search-btn {
        padding: 15px 20px;
        background: #3498db;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .gantt-search-btn:hover {
        background: #2980b9;
        transform: scale(1.05);
    }
    
    /* Navegación del mes - Optimizada para pantallas grandes */
    .gantt-month-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 20px 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border: 2px solid #ffffff;
        margin-left: auto;
        width: 100%;
        max-width: 800px;
    }

    .gantt-nav-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 25px;
        border: none;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .gantt-nav-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    
        font-size: 1rem; /* Aumentado */
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
        gap: 10px;
        padding: 15px 25px;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .gantt-month-display:before {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 10%;
        right: 10%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #ffffff, transparent);
        border-radius: 3px;
    }

    .gantt-current-month {
        font-size: 1.8rem;
        font-weight: 800;
        color: white;
        text-align: center;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        letter-spacing: 0.5px;
    }
        font-weight: 600;
        color: #333;
        font-size: 1.3rem; /* Aumentado */
        text-align: center;
    }
    
    /* Botón mes actual/hoy */
    .gantt-nav-btn-today {
        background: rgba(255, 255, 255, 0.9);
        color: #667eea;
        font-size: 1rem;
        padding: 12px 20px;
        border: 2px solid rgba(255, 255, 255, 0.8);
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 8px;
        backdrop-filter: blur(10px);
    }

    .gantt-nav-btn-today:hover {
        background: #ffffff;
        color: #4a6cf7;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
    }
    }
    
    /* Contenedor del Gantt - Mejorado para scroll óptimo */
    .gantt-chart-container {
        width: 100%;
        flex: 1;
        /* Quitar overflow: hidden para permitir scroll horizontal */
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: #ffffff;
        position: relative;
        margin: 0 10px;
        display: flex;
        flex-direction: column;
        min-width: 0;
        max-height: 80vh;
    }
    
    /* Cabecera de días - Mejorada para sticky completo */
    .gantt-days-header {
        position: sticky;
        top: 0;
        z-index: 280;
        background: #ffffff;
        border-bottom: 3px solid #3498db;
        display: flex;
        min-height: 80px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        flex-shrink: 0;
        /* Asegurar que se mantenga fijo en todos los casos */
        width: 100%;
        left: 0;
        right: 0;
    }
    
    .gantt-sidebar-header {
        width: 300px;
        min-width: 300px;
        background: #fff;
        color: #111;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        border-right: 3px solid #2c3e50;
        position: sticky;
        left: 0;
        z-index: 290;
        /* Asegurar que se mantenga fijo */
        top: 0;
        height: 80px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    .gantt-timeline-header {
        display: flex;
        min-width: {{ $totalDays * 50 }}px;
        width: {{ $totalDays * 50 }}px;
        background: #ecf0f1;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
        position: relative;
        top: 0;
        z-index: 275;
        scroll-behavior: smooth;
    }
    
    .gantt-day-column {
        width: 50px;
        min-width: 50px;
        height: 80px;
        border-right: 1px solid #bdc3c7;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #ecf0f1;
        transition: background-color 0.2s ease;
        position: relative;
        flex-shrink: 0; /* Evitar que se compriman las columnas */
    }
    
    .gantt-day-column:hover {
        background: #d5dbdb;
    }
    
    .gantt-day-column.weekend {
        background: #f39c12;
        color: white;
    }
    
    .gantt-day-column.weekend:hover {
        background: #e67e22;
    }
    
    /* Estilo especial para primer día del mes */
    .gantt-day-column.first-of-month {
        background: #3498db;
        color: white;
        border-right: 3px solid #2980b9;
        font-weight: 700;
    }
    .gantt-day-column.first-of-month .gantt-day-number {
        font-size: 1.2rem;
        font-weight: 800;
    }
    
    .gantt-day-column.first-of-month .gantt-day-name {
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .gantt-grid-column.first-of-month {
        border-right: 3px solid #3498db;
    }
    
    .gantt-day-column:last-child {
        border-right: none;
    }
    
    .gantt-day-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 2px;
    }
    
    .gantt-day-name {
        font-size: 0.8rem;
        font-weight: 500;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Cuerpo del Gantt - Optimizado para scroll sincronizado independiente */
    .gantt-body {
        display: flex;
        flex: 1;
        overflow: hidden; /* Sin scroll directo en el body */
        position: relative;
        height: calc(80vh - 140px);
        min-height: 400px;
        width: 100%;
    }
    
    /* Sidebar con información de maquinarias */
    .gantt-sidebar {
        width: 300px;
        min-width: 300px;
        background: #f8f9fa;
        border-right: 3px solid #2c3e50;
        overflow-y: auto; /* SCROLL VERTICAL habilitado */
        overflow-x: hidden; /* Sin scroll horizontal */
        position: sticky;
        left: 0;
        z-index: 270;
        flex-shrink: 0;
        height: 100%; /* Ocupar toda la altura del contenedor */
        /* Altura dinámica para contenido */
        @if(isset($centros))
            @php
                $totalSidebarHeight = 0;
                foreach($centros as $centro) {
                    $totalSidebarHeight += 54; // Header height
                    $totalSidebarHeight += $centro->maquinarias->count() * 60; // Maquinarias height
                }
            @endphp
            /* El contenido real del sidebar */
            max-height: calc(80vh - 140px);
        @else
            max-height: calc(80vh - 140px);
        @endif
    }
    
    .gantt-centro-group {
        margin-bottom: 2px;
    }
    
    .gantt-centro-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #34495e;
        color: white;
        margin-bottom: 0;
        cursor: pointer;
        transition: background-color 0.3s ease;
        user-select: none;
    }
    
    .gantt-centro-header:hover {
        background: #2c3e50;
    }
    
    .gantt-centro-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .gantt-centro-toggle {
        font-size: 1rem;
        transition: transform 0.3s ease;
        width: 20px;
        display: inline-block;
    }
    
    .gantt-centro-toggle.collapsed {
        transform: rotate(-90deg);
    }
    
    .gantt-centro-header h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .gantt-centro-maquinarias {
        transition: max-height 0.3s ease, opacity 0.3s ease;
        overflow: hidden;
        background: #ffffff;
        max-height: 1000px; /* Altura máxima por defecto */
    }
    
    .gantt-centro-maquinarias.collapsed {
        max-height: 0;
        opacity: 0;
    }
    
    .gantt-centro-count {
        font-size: 0.9rem;
        color: #ecf0f1;
        background: #2c3e50;
        padding: 4px 8px;
        border-radius: 12px;
        border: 1px solid #ecf0f1;
        font-weight: 600;
    }
    
    .gantt-maquinaria-row {
        padding: 8px 20px 12px 20px; /* Menos padding arriba, más abajo */
        border-bottom: 1px solid #dee2e6;
        transition: all 0.2s ease;
        cursor: pointer;
        background: #ffffff;
        height: 60px; /* Altura fija para alineación */
        display: flex;
        align-items: center;
        box-sizing: border-box;
    }
    
    .gantt-maquinaria-row:hover {
        background-color: #f8f9fa;
    }
    
    .gantt-maquinaria-row.has-tasks {
        background-color: #e8f5e8;
        border-left: 4px solid #28a745;
    }
    
    .gantt-maquinaria-name {
        font-weight: 500;
        font-size: 1rem;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
    }
    
    .gantt-empty-sidebar {
        padding: 30px 20px;
        color: #999;
        font-style: italic;
        text-align: center;
        font-size: 1.1rem;
    }
    
    /* Timeline con barras de tareas */
    .gantt-timeline {
        flex: 1;
        position: relative;
        overflow-x: auto !important; /* SCROLL HORIZONTAL habilitado */
        overflow-y: hidden;
        background: #fafafa;
        min-width: calc({{ $totalDays }} * 50px);
        width: 100%;
        height: 100%;
        border-left: 2px solid #ddd;
    }
    
    /* Grid de fondo */
    .gantt-grid {
        position: absolute;
        top: 17px; /* Ajuste: bajar la grilla unos pocos pixeles para alinear con las barras */
        left: 0;
        width: 100%;
        z-index: 1;
        min-width: calc({{ $totalDays }} * 50px);
        width: max(100%, calc({{ $totalDays }} * 50px));
        /* El grid debe poder moverse verticalmente con el scroll del sidebar */
        transform: translateY(0px); /* Será controlado por JavaScript */
        transition: transform 0.1s ease-out; /* Transición suave */
        @if(isset($centros))
            @php
                $totalHeight = 0;
                foreach($centros as $centro) {
                    $totalHeight += 54; // Header height
                    $totalHeight += $centro->maquinarias->count() * 60; // Maquinarias height
                }
            @endphp
            height: {{ $totalHeight }}px;
            min-height: {{ $totalHeight }}px;
        @else
            height: 400px;
            min-height: 400px;
        @endif
    }
    
    .gantt-grid-row {
        /* Elimina position absolute para que las filas se apilen verticalmente */
        position: relative;
        left: 0;
        width: 100%;
        height: 60px; /* Altura fija para alineación */
        display: flex;
        border-bottom: 1px solid #e0e0e0;
        min-width: calc({{ $totalDays }} * 50px); /* Ancho mínimo para scroll horizontal */
        width: max(100%, calc({{ $totalDays }} * 50px));
        overflow-x: hidden !important;
        overflow: -moz-scrollbars-none;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .gantt-grid-row::-webkit-scrollbar {
        display: none !important;
    }
    
    .gantt-grid-cell {
        width: 50px;
        min-width: 50px;
        height: 100%;
        border-right: 1px solid #e0e0e0;
        background: #ffffff;
        transition: background-color 0.2s ease;
        flex-shrink: 0; /* Evitar que se compriman las celdas */
        overflow: hidden !important;
    }
    
    .gantt-grid-cell:hover {
        background: #f8f9fa;
    }
    }
    
    .gantt-grid-cell.weekend {
        background: #f39c12;
        opacity: 0.3;
    }
    
    .gantt-grid-cell.first-of-month {
        background: #3498db;
        border-right: 3px solid #2980b9;
        opacity: 0.5;
    }
    
    .gantt-grid-column {
        width: 50px;
        min-width: 50px;
        height: 100%;
        border-right: 1px solid #e0e0e0;
    }
    
    .gantt-grid-column.weekend {
        background: #f39c12;
        opacity: 0.3;
    }
    
    /* Barras de tareas - Aumentadas en tamaño */
    .gantt-task-bar {
        position: absolute;
        height: 45px;
        background-color: #4a6cf7;
        border-radius: 8px;
        z-index: 10;
        cursor: move;
        color: white;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        /* Centrering vertical mejorado */
        top: 50%;
        transform: translateY(-50%);
        margin-top: 0;
        /* Preparado para transformaciones de scroll */
        will-change: transform;
    }
    
    .gantt-task-bar:hover {
        box-shadow: 0 6px 15px rgba(0,0,0,0.25);
        border-color: #ffffff;
        /* Mantener transformación durante hover */
        transform: translateY(-50%) translateY(-2px);
    }
    
    .gantt-task-bar[data-activo="0"] {
        background-color: #a0a0a0 !important;
        opacity: 0.7;
        cursor: not-allowed !important;
    }
    
    .gantt-task-bar[data-activo="0"] .gantt-task-resizer {
        display: none !important;
    }
    
    /* Colores específicos por centro */
    .gantt-task-bar[data-centro="PRENSA"] {
        background-color: #ff6b6b;
    }
    
    .gantt-task-bar[data-centro="REVESTIMIENTO"] {
        background-color: #4ecdc4;
    }
    
    .gantt-task-bar[data-centro="POLIURETANO"] {
        background-color: #45b7d1;
    }
    
    .gantt-task-bar[data-centro="TRAFILA"] {
        background-color: #96ceb4;
    }
    
    .gantt-task-bar[data-centro="ANILLOS"] {
        background-color: #feca57;
    }
    
    /* Efecto de conflicto */
    .gantt-task-bar.conflict {
        background-color: #e74c3c !important;
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    /* Indicador de día actual durante scroll */
    .current-day-indicator {
        position: fixed !important;
        top: 70px !important;
        right: 20px !important;
        background: linear-gradient(135deg, #4a6cf7 0%, #3b82f6 100%) !important;
        color: white !important;
        padding: 10px 15px !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        z-index: 350 !important;
        box-shadow: 0 4px 12px rgba(74, 108, 247, 0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        backdrop-filter: blur(10px) !important;
        transition: all 0.2s ease !important;
    }
    
    .current-day-indicator .day-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }
    
    .current-day-indicator .day-num {
        font-size: 16px;
        font-weight: bold;
        line-height: 1;
    }
    
    .current-day-indicator .day-label {
        font-size: 10px;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .current-day-indicator .day-progress {
        font-size: 9px;
        opacity: 0.8;
        background: rgba(255, 255, 255, 0.2);
        padding: 1px 4px;
        border-radius: 3px;
        margin-top: 2px;
    }
    
    /* Indicador de maquinaria ocupada */
    .gantt-maquinaria-row.occupied {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
    }
    
    .gantt-maquinaria-row.occupied .gantt-maquinaria-name {
        font-weight: 600;
    }
    
    .gantt-task-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        height: 100%;
        padding: 5px 15px;
        position: relative;
        z-index: 2;
    }
    
    .gantt-task-label {
        font-size: 1rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        letter-spacing: 0.3px;
    }
    
    .gantt-task-dates {
        font-size: 0.8rem;
        font-weight: 500;
        opacity: 0.9;
        white-space: nowrap;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    
    /* Redimensionadores de tareas */
    .gantt-task-resizer {
        position: absolute;
        top: 0;
        width: 8px; /* Aumentado */
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
    
    /* Modal - Aumentado para pantallas grandes */
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
        margin: 8% auto;
        padding: 30px; /* Aumentado */
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        width: 80%;
        max-width: 700px; /* Aumentado */
        position: relative;
    }
    
    .gantt-modal-close {
        position: absolute;
        top: 20px;
        right: 25px;
        font-size: 1.8rem; /* Aumentado */
        color: #aaa;
        cursor: pointer;
    }
    
    .gantt-modal-close:hover {
        color: #555;
    }
    
    /* Formulario */
    .gantt-form-group {
        margin-bottom: 18px; /* Aumentado */
        flex: 1;
    }
    
    .gantt-form-row {
        display: flex;
        gap: 20px; /* Aumentado */
        margin-bottom: 18px;
    }
    
    .gantt-form-label {
        display: block;
        font-size: 1rem; /* Aumentado */
        color: #555;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .gantt-form-input, .gantt-form-textarea {
        width: 100%;
        padding: 12px 16px; /* Aumentado */
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem; /* Aumentado */
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
        min-height: 100px; /* Aumentado */
    }
    
    .gantt-form-footer {
        margin-top: 25px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .required {
        color: #d32f2f;
    }
    
    /* Notificaciones */
    .gantt-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px; /* Aumentado */
        background: #4a6cf7;
        color: white;
        border-radius: 8px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        z-index: 1100;
        transform: translateY(-20px);
        opacity: 0;
        transition: all 0.3s ease;
        font-size: 1rem; /* Aumentado */
    }

    .gantt-notification-success {
        background: #4caf50;
    }

    .gantt-notification-error {
        background: #f44336;
    }

    /* Estilos para el botón de confirmación - Aumentados */
    .gantt-confirm-action {
        position: absolute;
        right: -40px; /* Aumentado */
        top: 0;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .gantt-confirm-btn {
        width: 35px; /* Aumentado */
        height: 35px;
        border-radius: 50%;
        background-color: #4caf50;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px; /* Aumentado */
        border: none;
        box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .gantt-confirm-btn:hover {
        background-color: #3d8b40;
        transform: scale(1.15);
    }

    .gantt-cancel-btn {
        width: 35px; /* Aumentado */
        height: 35px;
        border-radius: 50%;
        background-color: #f44336;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px; /* Aumentado */
        border: none;
        box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .gantt-cancel-btn:hover {
        background-color: #d32f2f;
        transform: scale(1.15);
    }

    .gantt-comment-btn {
        width: 35px; /* Aumentado */
        height: 35px;
        border-radius: 50%;
        background-color: #ff9800;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px; /* Aumentado */
        border: none;
        box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .gantt-comment-btn:hover {
        background-color: #e68a00;
        transform: scale(1.15);
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
        margin: 8% auto;
        padding: 25px; /* Aumentado */
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        width: 80%;
        max-width: 600px; /* Aumentado */
    }

    .gantt-comment-textarea {
        width: 100%;
        padding: 15px; /* Aumentado */
        border: 1px solid #ddd;
        border-radius: 6px;
        min-height: 120px; /* Aumentado */
        margin: 15px 0;
        font-family: inherit;
        font-size: 1rem; /* Aumentado */
    }

    .gantt-comment-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }

    /* Forzar scroll horizontal en dispositivos de pantalla grande */
    @media (min-width: 1024px) {
        .gantt-timeline-header {
            overflow-x: scroll !important;
        }
        
        .gantt-timeline {
            overflow-x: scroll !important;
        }
        
        .gantt-grid {
            overflow-x: visible !important;
        }
        
        .gantt-grid-row {
            overflow-x: visible !important;
        }
    }
    
    /* Asegurar que el scroll horizontal siempre esté visible */
    .gantt-timeline::-webkit-scrollbar {
        height: 12px;
        width: 12px;
    }
    
    .gantt-timeline::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }
    
    .gantt-timeline::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 6px;
    }
    
    .gantt-timeline::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .gantt-timeline-header::-webkit-scrollbar {
        height: 8px;
    }
    
    .gantt-timeline-header::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .gantt-timeline-header::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .gantt-timeline-header::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Estilos para el scroll del sidebar */
    .gantt-sidebar::-webkit-scrollbar {
        width: 8px;
    }
    
    .gantt-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .gantt-sidebar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .gantt-sidebar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Estilos para el scroll del contenedor principal */
    .gantt-body::-webkit-scrollbar {
        height: 12px;
        width: 12px;
    }
    
    .gantt-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }
    
    .gantt-body::-webkit-scrollbar-thumb {
        background: #4a6cf7;
        border-radius: 6px;
    }
    
    .gantt-body::-webkit-scrollbar-thumb:hover {
        background: #3498db;
    }
    
    .gantt-chart-container::-webkit-scrollbar {
        height: 12px;
        width: 12px;
    }
    
    .gantt-chart-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }
    
    .gantt-chart-container::-webkit-scrollbar-thumb {
        background: #4a6cf7;
        border-radius: 6px;
    }
    
    .gantt-chart-container::-webkit-scrollbar-thumb:hover {
        background: #3498db;
    }
    
    /* Mejorar la experiencia de scroll */
    .gantt-timeline, .gantt-sidebar, .gantt-body, .gantt-chart-container {
        scroll-behavior: smooth;
    }
    
    /* Asegurar que las cabeceras permanezcan fijas */
    .gantt-days-header {
        position: sticky;
        top: 0;
        z-index: 300;
        background: #ffffff;
        border-bottom: 3px solid #3498db;
        display: flex;
        min-height: 80px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }
    
    .gantt-sidebar-header {
        position: sticky;
        top: 0;
        z-index: 310;
        background: #34495e;
    }
    
    /* Asegurar que el contenido del gantt-body tenga la altura correcta para scroll */
    .gantt-body {
        @if(isset($centros))
            @php
                $totalContentHeight = 0;
                foreach($centros as $centro) {
                    $totalContentHeight += 54; // Header height
                    $totalContentHeight += $centro->maquinarias->count() * 60; // Maquinarias height
                }
            @endphp
            /* Si el contenido es más alto que el contenedor, mostrar scroll */
            max-height: calc(100vh - 200px);
        @endif
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prevSemesterBtn = document.getElementById('prevSemester');
    const nextSemesterBtn = document.getElementById('nextSemester');
    const currentSemesterDisplay = document.getElementById('currentSemesterDisplay');
    const ganttTimeline = document.getElementById('ganttTimeline');
    
    // Función para colapsar/expandir grupos de centros
    function toggleCentroGroup(centroId) {
        const maquinariasContainer = document.getElementById(`centro-maquinarias-${centroId}`);
        const toggleIcon = document.querySelector(`[data-centro-id="${centroId}"] .gantt-centro-toggle`);
        if (maquinariasContainer.classList.contains('collapsed')) {
            // Expandir
            maquinariasContainer.classList.remove('collapsed');
            maquinariasContainer.style.maxHeight = maquinariasContainer.scrollHeight + 'px';
            toggleIcon.classList.remove('collapsed');
        } else {
            // Colapsar
            maquinariasContainer.style.maxHeight = maquinariasContainer.scrollHeight + 'px';
            // Forzar reflow
            maquinariasContainer.offsetHeight;
            maquinariasContainer.style.maxHeight = '0px';
            maquinariasContainer.classList.add('collapsed');
            toggleIcon.classList.add('collapsed');
        }
        // Actualizar posiciones de las barras de tareas después de colapsar/expandir
        setTimeout(() => {
            updateTaskPositions();
        }, 300);
    }
    
    // Función para actualizar las posiciones de las barras de tareas
    function updateTaskPositions() {
        window.maquinariaPositions = {};
        let currentPosition = 0;
        // Ajuste: offset para alinear perfectamente el sidebar y las barras con la grilla
        const GRID_ROW_HEIGHT = 60;
        const BAR_HEIGHT = 45; // Altura real de la barra
        let sidebarOffset = 0;
        // Detectar si hay un header sticky en el sidebar
        const sidebarHeader = document.querySelector('.gantt-sidebar-header');
        if (sidebarHeader) {
            sidebarOffset = (sidebarHeader.offsetHeight || 0) - 18; // Subimos el sidebar 12px más
        }
        let accumulatedTop = sidebarOffset;
        document.querySelectorAll('.gantt-centro-group').forEach(centroGroup => {
            // Sumar la altura del header del centro antes de las maquinarias
            accumulatedTop += 54; // CENTRO_HEADER_HEIGHT
            const maquinariasContainer = centroGroup.querySelector('.gantt-centro-maquinarias');
            if (!maquinariasContainer.classList.contains('collapsed')) {
                // Solo contar maquinarias si el grupo está expandido
                const maquinariaRows = maquinariasContainer.querySelectorAll('.gantt-maquinaria-row');
                maquinariaRows.forEach(row => {
                    const maquinariaId = row.getAttribute('data-maquinaria-id');
                    window.maquinariaPositions[maquinariaId] = accumulatedTop + (GRID_ROW_HEIGHT - BAR_HEIGHT) / 2;
                    accumulatedTop += GRID_ROW_HEIGHT;
                });
            }
        });
        // Actualizar posiciones de todas las barras de tareas
        document.querySelectorAll('.gantt-task-bar').forEach(taskBar => {
            const maquinariaId = taskBar.getAttribute('data-maquinaria-id');
            if (maquinariaId && maquinariaPositions[maquinariaId] !== undefined) {
                taskBar.style.top = maquinariaPositions[maquinariaId] + 'px';
                taskBar.style.display = 'block';
            } else if (maquinariaId) {
                // Ocultar barras de tareas si el centro está colapsado
                taskBar.style.display = 'none';
            }
        });
        // Ajuste visual: subir el sidebar para alinear con la grilla
        const sidebar = document.querySelector('.gantt-sidebar');
        if (sidebar) {
            sidebar.style.marginTop = sidebarOffset + 'px';
        }
        console.log('Posiciones de tareas actualizadas, altura total:', accumulatedTop);
    }
    
    // Hacer la función global para poder usarla desde el HTML
    window.toggleCentroGroup = toggleCentroGroup;
    
    // Sincronizar scroll entre header y timeline - MEJORADO
    const timelineHeader = document.querySelector('.gantt-timeline-header');
    const timelineContent = document.querySelector('.gantt-timeline');
    const ganttBody = document.querySelector('.gantt-body');
    
    if (timelineHeader && timelineContent && ganttBody) {
        console.log('✅ Elementos encontrados para scroll:');
        console.log('- Timeline Header:', timelineHeader);
        console.log('- Timeline Content:', timelineContent);
        console.log('- Gantt Body:', ganttBody);
        
        // Variable para evitar loops infinitos
        let isScrolling = false;
        
        // Función para calcular y mostrar el día preciso durante el scroll
        function calculateCurrentDay(scrollLeft) {
            const dayColumns = document.querySelectorAll('.gantt-day-column');
            const dayWidth = dayColumns.length > 0 ? dayColumns[0].offsetWidth : 50;
            
            // Calcular el día actual basado en la posición del scroll
            const currentDayIndex = Math.floor(scrollLeft / dayWidth);
            const dayOffset = (scrollLeft % dayWidth) / dayWidth;
            
            if (dayColumns[currentDayIndex]) {
                const dayElement = dayColumns[currentDayIndex];
                const dayNumber = dayElement.querySelector('.gantt-day-number')?.textContent;
                const dayName = dayElement.querySelector('.gantt-day-name')?.textContent;
                
                // Remover indicador anterior
                document.querySelectorAll('.current-day-indicator').forEach(el => el.remove());
                
                // Crear indicador de día actual
                const indicator = document.createElement('div');
                indicator.className = 'current-day-indicator';
                indicator.style.cssText = `
                    position: absolute;
                    left: ${scrollLeft}px;
                    top: 0;
                    width: 2px;
                    height: 100%;
                    background-color: #ff4757;
                    z-index: 1000;
                    pointer-events: none;
                `;
                
                const timeline = document.querySelector('.gantt-timeline');
                if (timeline) {
                    timeline.appendChild(indicator);
                }
                
                // Mostrar información del día actual en la consola para depuración
                console.log(`📅 Día actual: ${dayNumber} ${dayName} (Índice: ${currentDayIndex}, Offset: ${(dayOffset * 100).toFixed(1)}%)`);
                
                // Opcional: Actualizar algún indicador visual
                updateDayIndicator(currentDayIndex, dayNumber, dayName, dayOffset);
            }
        }
        
        // Función para actualizar indicador visual del día actual
        function updateDayIndicator(dayIndex, dayNumber, dayName, offset) {
            // Remover indicador anterior
            const prevIndicator = document.querySelector('.current-day-indicator');
            if (prevIndicator) {
                prevIndicator.remove();
            }
            
            // Crear nuevo indicador
            const indicator = document.createElement('div');
            indicator.className = 'current-day-indicator';
            indicator.innerHTML = `
                <div class="day-info">
                    <span class="day-num">${dayNumber}</span>
                    <span class="day-label">${dayName}</span>
                    <span class="day-progress">${(offset * 100).toFixed(0)}%</span>
                </div>
            `;
            
            // Posicionar el indicador
            const timelineHeader = document.querySelector('.gantt-timeline-header');
            if (timelineHeader) {
                timelineHeader.appendChild(indicator);
                indicator.style.position = 'fixed';
                indicator.style.top = '10px';
                indicator.style.right = '20px';
                indicator.style.background = 'rgba(74, 108, 247, 0.9)';
                indicator.style.color = 'white';
                indicator.style.padding = '8px 12px';
                indicator.style.borderRadius = '6px';
                indicator.style.fontSize = '12px';
                indicator.style.zIndex = '300';
                indicator.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
            }
        }
        
        // Función para sincronizar el scroll vertical (sidebar -> grid)
        function syncVerticalScroll(scrollTop) {
            const ganttGrid = document.querySelector('.gantt-grid');
            const taskBars = document.querySelectorAll('.gantt-task-bar');
            
            if (ganttGrid) {
                // Mover el grid verticalmente (negativo porque el grid debe moverse opuesto al scroll)
                ganttGrid.style.transform = `translateY(-${scrollTop}px)`;
                
                // Mover todas las barras de tareas también para mantener alineación
                taskBars.forEach(taskBar => {
                    // Obtener la posición original de la tarea
                    const originalTop = taskBar.getAttribute('data-original-top') || taskBar.style.top;
                    if (!taskBar.getAttribute('data-original-top')) {
                        taskBar.setAttribute('data-original-top', taskBar.style.top);
                    }
                    
                    // Aplicar el offset del scroll
                    const originalTopValue = parseFloat(originalTop) || 0;
                    const newTop = originalTopValue - scrollTop;
                    taskBar.style.transform = `translateY(${-scrollTop}px)`;
                });
                
                console.log('📐 Scroll Vertical sincronizado:', scrollTop);
            }
        }
        
        // Función para sincronizar el scroll horizontal (solo header)
        function syncHorizontalScroll(scrollLeft) {
            // Sincronizar el header de días horizontalmente
            timelineHeader.scrollLeft = scrollLeft;
            
            console.log('📏 Scroll Horizontal sincronizado:', scrollLeft);
            
            // Calcular día preciso
            calculateCurrentDay(scrollLeft);
        }
        
        // Sidebar scroll (vertical) - controla el movimiento del grid
        const sidebar = document.querySelector('.gantt-sidebar');
        if (sidebar) {
            sidebar.addEventListener('scroll', function(e) {
                if (!isScrolling) {
                    isScrolling = true;
                    // Solo sincronizar scroll vertical
                    syncVerticalScroll(this.scrollTop);
                    
                    requestAnimationFrame(() => {
                        isScrolling = false;
                    });
                }
            });
        }
        
        // Timeline scroll (horizontal) - controla el movimiento de los días
        timelineContent.addEventListener('scroll', function(e) {
            if (!isScrolling) {
                isScrolling = true;
                // Solo sincronizar scroll horizontal
                syncHorizontalScroll(this.scrollLeft);
                
                requestAnimationFrame(() => {
                    isScrolling = false;
                });
            }
        });
        
        // Header de timeline scroll (horizontal) - sincronizar de vuelta al timeline
        timelineHeader.addEventListener('scroll', function(e) {
            if (!isScrolling) {
                isScrolling = true;
                // Sincronizar timeline horizontal
                timelineContent.scrollLeft = this.scrollLeft;
                console.log('🔄 Scroll Header -> Timeline:', this.scrollLeft);
                
                // Calcular día preciso
                calculateCurrentDay(this.scrollLeft);
                
                requestAnimationFrame(() => {
                    isScrolling = false;
                });
            }
        });
        
        // Gantt Body scroll - dividir en horizontal y vertical
        ganttBody.addEventListener('scroll', function(e) {
            if (!isScrolling) {
                isScrolling = true;
                
                // Sincronizar scroll horizontal con header de días
                if (this.scrollLeft !== timelineHeader.scrollLeft) {
                    syncHorizontalScroll(this.scrollLeft);
                }
                
                // Sincronizar scroll vertical con sidebar
                if (sidebar && this.scrollTop !== sidebar.scrollTop) {
                    sidebar.scrollTop = this.scrollTop;
                    syncVerticalScroll(this.scrollTop);
                }
                
                requestAnimationFrame(() => {
                    isScrolling = false;
                });
            }
        });
        
        console.log('✅ Scroll vertical y horizontal sincronizado correctamente');
        
        // Inicializar posiciones originales de las barras de tareas
        document.querySelectorAll('.gantt-task-bar').forEach(taskBar => {
            if (!taskBar.getAttribute('data-original-top')) {
                taskBar.setAttribute('data-original-top', taskBar.style.top);
            }
        });
    } else {
        console.error('❌ No se pudieron encontrar los elementos para scroll:', {
            timelineHeader: !!timelineHeader,
            timelineContent: !!timelineContent,
            ganttBody: !!ganttBody
        });
    }
    
    // Función para optimizar el comportamiento sticky (inspirada en tu código)
    function optimizeStickyElements() {
        const ganttContainer = document.querySelector('.gantt-chart-container');
        const sidebar = document.querySelector('.gantt-sidebar');
        const sidebarHeader = document.querySelector('.gantt-sidebar-header');
        const daysHeader = document.querySelector('.gantt-days-header');
        
        if (ganttContainer && sidebar && sidebarHeader && daysHeader) {
            // Forzar posicionamiento sticky en elementos críticos
            sidebar.style.position = 'sticky';
            sidebar.style.left = '0';
            sidebar.style.zIndex = '270';
            sidebar.style.background = '#f8f9fa';
            
            sidebarHeader.style.position = 'sticky';
            sidebarHeader.style.left = '0';
            sidebarHeader.style.top = '0';
            sidebarHeader.style.zIndex = '290';
            
            daysHeader.style.position = 'sticky';
            daysHeader.style.top = '0';
            daysHeader.style.zIndex = '280';
            daysHeader.style.background = '#ffffff';
            
            console.log('Elementos sticky optimizados');
        }
    }
    
    // Función para asegurar dimensiones correctas del scroll
    function ensureScrollDimensions() {
        const timelineContent = document.querySelector('.gantt-timeline');
        const timelineHeader = document.querySelector('.gantt-timeline-header');
        const ganttGrid = document.querySelector('.gantt-grid');
        
        if (timelineContent && timelineHeader) {
            const totalDays = {{ $totalDays }};
            const dayWidth = 50; // 50px por día
            const totalWidth = totalDays * dayWidth;
            
            // Forzar el ancho mínimo en todos los elementos
            timelineHeader.style.minWidth = totalWidth + 'px';
            
            if (ganttGrid) {
                ganttGrid.style.minWidth = totalWidth + 'px';
                ganttGrid.style.width = totalWidth + 'px';
            }
            
            // Asegurar que todas las filas del grid tengan el ancho correcto
            const gridRows = document.querySelectorAll('.gantt-grid-row');
            gridRows.forEach(row => {
                row.style.minWidth = totalWidth + 'px';
                row.style.width = totalWidth + 'px';
            });
        }
    }
    
    // Ejecutar optimizaciones al cargar la página
    optimizeStickyElements();
    ensureScrollDimensions();
    updateTaskPositions(); // <-- Forzar posiciones correctas al cargar

    // Ejecutar la función cuando se redimensione la ventana
    window.addEventListener('resize', function() {
        optimizeStickyElements();
        ensureScrollDimensions();
        updateTaskPositions(); // <-- Forzar posiciones correctas al redimensionar
    });

    // Ejecutar la función después de que el DOM esté completamente cargado
    setTimeout(() => {
        optimizeStickyElements();
        ensureScrollDimensions();
        updateTaskPositions(); // <-- Forzar posiciones correctas tras timeout
    }, 100);
    
    // Variables para seguimiento de semestres
    let currentSemester = {{ $currentSemester }};
    let currentYear = {{ $currentYear }};
    let totalDays = {{ $totalDays }};
    
    // El mapeo de posiciones de maquinaria se hace solo en JS dinámicamente con updateTaskPositions()
    
    // Declarar las variables faltantes
    let draggingTask = null;
    let resizing = null;
    let initialX = 0;
    let initialY = 0;
    let initialLeft = 0;
    let initialTop = 0;
    let initialWidth = 0;
    let draggedToNewMaquinaria = false;
    
    // Función de formato de fecha
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
            return;
        }

        taskBar.addEventListener('mousedown', function(e) {
            if (taskBar.querySelector('.gantt-confirm-action')) return;
            draggingTask = null;
            resizing = null;
            draggedToNewMaquinaria = false;

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
                // Iniciar arrastre solo horizontal
                draggingTask = this;
                // Forzar left y width a porcentaje si no lo están
                if (!this.style.left || this.style.left.indexOf('%') === -1) {
                    let leftPx = this.offsetLeft;
                    let parentW = this.parentElement ? this.parentElement.offsetWidth : 1;
                    let leftPct = (leftPx / parentW) * 100;
                    this.style.left = leftPct + '%';
                }
                if (!this.style.width || this.style.width.indexOf('%') === -1) {
                    let widthPx = this.offsetWidth;
                    let parentW = this.parentElement ? this.parentElement.offsetWidth : 1;
                    let widthPct = (widthPx / parentW) * 100;
                    this.style.width = widthPct + '%';
                }
                // Guardar maquinaria original
                draggingTask.setAttribute('data-original-maquinaria-id', draggingTask.getAttribute('data-maquinaria-id'));
                // Guardar el ancho original de la barra al iniciar el drag
                let barWidth = parseFloat(this.style.width);
                if (isNaN(barWidth) || barWidth <= 0) {
                    if (this.parentElement && this.parentElement.offsetWidth) {
                        barWidth = (this.offsetWidth / this.parentElement.offsetWidth) * 100;
                    } else {
                        barWidth = 10;
                    }
                }
                draggingTask.setAttribute('data-original-width', barWidth);
                // Calcular el offset del mouse dentro de la barra (en % del contenedor)
                let parentW = this.parentElement ? this.parentElement.offsetWidth : 1;
                let mouseOffsetPx = e.clientX - this.getBoundingClientRect().left;
                let mouseOffsetPct = (mouseOffsetPx / parentW) * 100;
                draggingTask.setAttribute('data-mouse-offset', mouseOffsetPct);
                initialX = e.clientX;
                initialLeft = parseFloat(this.style.left);
                // Guardar el top original en px
                draggingTask.setAttribute('data-original-top-px', this.style.top);
                // Calcular offset vertical entre el mouse y el centro visual de la barra (por transform: translateY(-50%))
                let barRect = this.getBoundingClientRect();
                const rowHeight = 60; // GRID_ROW_HEIGHT
                const barHeight = barRect.height;
                // El centro de la barra debe coincidir con el mouse, así que offsetY = e.clientY - (barRect.top + barRect.height / 2)
                // Si hay desfase, restar la diferencia completa (no la mitad)
                let offsetY = e.clientY - (barRect.top + barHeight / 2);
                if (rowHeight > barHeight) {
                    offsetY -= (rowHeight - barHeight);
                }
                draggingTask.setAttribute('data-mouse-offset-y', offsetY);
            }
            e.preventDefault();
        });
    }
    
    document.addEventListener('mousemove', function(e) {
        if (!draggingTask && !resizing) return;

        const pendingConfirmations = document.querySelectorAll('.gantt-confirm-action');
        if (pendingConfirmations.length > 0) {
            draggingTask = null;
            resizing = null;
            return;
        }

        if (draggingTask) {
            // Hacer que la barra siga el mouse exactamente donde se hizo clic
            const timelineWidth = ganttTimeline.clientWidth;
            let barWidth = parseFloat(draggingTask.getAttribute('data-original-width'));
            if (isNaN(barWidth) || barWidth <= 0) {
                barWidth = 10;
            }
            // Offset del mouse dentro de la barra (en % del contenedor)
            let mouseOffsetPct = parseFloat(draggingTask.getAttribute('data-mouse-offset'));
            if (isNaN(mouseOffsetPct)) mouseOffsetPct = 0;
            // Calcular el left para que el mouse quede donde se agarró la barra
            let parentW = draggingTask.parentElement ? draggingTask.parentElement.offsetWidth : timelineWidth;
            let mouseX = e.clientX - draggingTask.parentElement.getBoundingClientRect().left;
            let newLeft = (mouseX - (mouseOffsetPct / 100) * parentW) / parentW * 100;
            newLeft = Math.max(0, Math.min(100 - barWidth, newLeft));

            // Detectar la fila (maquinaria) bajo el mouse usando getBoundingClientRect para precisión absoluta
            let targetRow = null;
            let targetMaquinariaId = draggingTask.getAttribute('data-original-maquinaria-id');
            const rows = Array.from(document.querySelectorAll('.gantt-grid-row'));
            for (let row of rows) {
                const rect = row.getBoundingClientRect();
                if (e.clientY >= rect.top && e.clientY <= rect.bottom) {
                    targetRow = row;
                    targetMaquinariaId = row.getAttribute('data-maquinaria-id');
                    break;
                }
            }
            // Calcular el centro visual exacto de la fila
            let newTop = null;
            if (targetRow) {
                const rowRect = targetRow.getBoundingClientRect();
                const gridRect = targetRow.parentElement.getBoundingClientRect();
                // Centro de la fila relativo al contenedor de la grilla
                const rowCenter = (rowRect.top + rowRect.bottom) / 2 - gridRect.top;
                // Centrar la barra en la fila
                const barHeight = draggingTask.offsetHeight;
                newTop = rowCenter;
                draggingTask.setAttribute('data-maquinaria-id', targetMaquinariaId);
                draggingTask.style.backgroundColor = '#ff9800';
                draggingTask.style.opacity = '0.8';
            } else {
                // Si no está sobre ninguna fila, mantener la original
                targetMaquinariaId = draggingTask.getAttribute('data-original-maquinaria-id');
                draggingTask.setAttribute('data-maquinaria-id', targetMaquinariaId);
                draggingTask.style.backgroundColor = '';
                draggingTask.style.opacity = '1';
                if (typeof maquinariaPositions !== 'undefined' && maquinariaPositions[targetMaquinariaId] !== undefined) {
                    newTop = maquinariaPositions[targetMaquinariaId] + 30; // fallback
                } else {
                    // Restaurar top original en px si no hay posición
                    let origTop = draggingTask.getAttribute('data-original-top-px');
                    if (origTop !== null && origTop !== undefined) {
                        newTop = parseFloat(origTop);
                    }
                }
            }
            if (newTop !== null) {
                draggingTask.style.top = newTop + 'px';
                draggingTask.style.transform = 'translateY(-50%)';
            }
            draggingTask.style.left = newLeft + '%';
            // No modificar el ancho aquí
            updateTaskDates(draggingTask, barWidth);
        } else if (resizing) {
            // Solo modificar el ancho, no la posición horizontal salvo que sea resize izquierdo
            const dx = e.clientX - initialX;
            const timelineWidth = ganttTimeline.clientWidth;
            const percentageMoved = (dx / timelineWidth) * 100;
            const minWidth = 100 / totalDays; // Un día mínimo
            if (resizing.isLeft) {
                let newLeft = initialLeft + percentageMoved;
                let newWidth = initialWidth - percentageMoved;
                // Limitar para que no se salga del grid ni desaparezca
                if (newLeft < 0) {
                    newWidth += newLeft; // Resta lo que se salió
                    newLeft = 0;
                }
                newWidth = Math.max(minWidth, newWidth);
                if (newLeft + newWidth > 100) {
                    newWidth = 100 - newLeft;
                }
                resizing.task.style.left = newLeft + '%';
                resizing.task.style.width = newWidth + '%';
                updateTaskDates(resizing.task);
            } else {
                let newWidth = initialWidth + percentageMoved;
                newWidth = Math.max(minWidth, Math.min(100 - initialLeft, newWidth));
                resizing.task.style.width = newWidth + '%';
                updateTaskDates(resizing.task);
            }
        }
    });
    
    document.addEventListener('mouseup', function(e) {
        if (draggingTask) {
            // Detectar la fila real bajo el mouse al soltar, solo contando filas de maquinaria reales
            let targetRow = null;
            let targetMaquinariaId = draggingTask.getAttribute('data-original-maquinaria-id');
            // Solo filas de maquinaria visibles
            const rows = Array.from(document.querySelectorAll('.gantt-maquinaria-row')).filter(row => row.offsetParent !== null);
            const mouseY = e.clientY;
            let debugRows = [];
            for (let row of rows) {
                const rect = row.getBoundingClientRect();
                debugRows.push({id: row.getAttribute('data-maquinaria-id'), top: rect.top, bottom: rect.bottom});
                if (mouseY >= rect.top && mouseY <= rect.bottom) {
                    targetRow = row;
                    targetMaquinariaId = row.getAttribute('data-maquinaria-id');
                    break;
                }
            }
            console.log('DEBUG filas maquinaria visibles:', debugRows);
            console.log('DEBUG mouseY:', mouseY);
            console.log('Fila detectada mouseup:', targetMaquinariaId, targetRow);
            // Asignar la maquinaria y centrar la barra en la fila (corrigiendo desfase vertical)
            if (targetRow && typeof window.maquinariaPositions !== 'undefined' && window.maquinariaPositions[targetMaquinariaId] !== undefined) {
                draggingTask.setAttribute('data-maquinaria-id', targetMaquinariaId);
                // Centrar perfectamente la barra en la fila
                const rowHeight = targetRow.offsetHeight || 60;
                const barHeight = draggingTask.offsetHeight;
                const offset = (rowHeight - barHeight) / 2;
                draggingTask.style.top = (window.maquinariaPositions[targetMaquinariaId] + offset) + 'px';
                draggingTask.style.transform = '';
            }
            draggingTask.style.opacity = '1';
            draggingTask.style.backgroundColor = '';
            // Restaurar el ancho original al terminar el drag
            let barWidth = parseFloat(draggingTask.getAttribute('data-original-width'));
            if (!isNaN(barWidth) && barWidth > 0) {
                draggingTask.style.width = barWidth + '%';
            }
            updateTaskDates(draggingTask, barWidth);
            updateTaskDatesInDB(draggingTask);
            // Limpiar el atributo temporal
            draggingTask.removeAttribute('data-original-width');
            draggingTask = null;
        }
        if (resizing) {
            updateTaskDates(resizing.task);
            updateTaskDatesInDB(resizing.task);
            resizing = null;
        }
        draggedToNewMaquinaria = false;
    });
    
    function updateTaskDates(taskBar, forcedWidth = null) {
        // Obtener left y width de forma robusta
        let left = parseFloat(taskBar.style.left);
        let width = forcedWidth !== null ? forcedWidth : parseFloat(taskBar.style.width);
        if (isNaN(left)) left = 0;
        if (isNaN(width) || width <= 0) {
            if (taskBar.parentElement && taskBar.parentElement.offsetWidth) {
                width = (taskBar.offsetWidth / taskBar.parentElement.offsetWidth) * 100;
            } else {
                width = 100 / totalDays;
            }
        }
        width = Math.max(width, 100 / totalDays);

        // Calcular días desde el inicio del semestre
        const startDay = Math.max(1, Math.ceil(left / 100 * totalDays));
        const endDay = Math.min(totalDays, Math.floor((left + width) / 100 * totalDays));

        // Crear fechas basadas en el semestre
        const semesterStart = new Date(currentYear, (currentSemester - 1) * 6, 1);
        const startDate = new Date(semesterStart);
        startDate.setDate(startDate.getDate() + startDay - 1);

        const endDate = new Date(semesterStart);
        endDate.setDate(endDate.getDate() + endDay - 1);

        // Formatear fechas para mostrar
        const startFormatted = `${startDate.getDate()}/${startDate.getMonth() + 1}`;
        const endFormatted = `${endDate.getDate()}/${endDate.getMonth() + 1}`;

        const datesSpan = taskBar.querySelector('.gantt-task-dates');
        if (datesSpan) {
            datesSpan.textContent = `${startFormatted} - ${endFormatted}`;
        }

        taskBar.setAttribute('data-start-date', formatDate(startDate));
        taskBar.setAttribute('data-end-date', formatDate(endDate));
    }
    
    function updateTaskDatesInDB(taskBar) {
        const taskId = taskBar.getAttribute('data-task-id');
        const startDate = taskBar.getAttribute('data-start-date');
        const endDate = taskBar.getAttribute('data-end-date');
        const maquinariaId = taskBar.getAttribute('data-maquinaria-id');
        
        console.log('Enviando actualización para tarea:', {
            id: taskId,
            finicio: startDate,
            ftermino: endDate,
            maquinaria_id: maquinariaId
        });
        
        const formData = new FormData();
        formData.append('finicio', startDate);
        formData.append('ftermino', endDate);
        if (maquinariaId) {
            formData.append('maquinaria_id', maquinariaId);
        }
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        let baseUrl = window.location.origin;
        const url = baseUrl.includes('localhost') 
            ? `${baseUrl}/compromops/${taskId}/ajax-update`
            : `${baseUrl}/example-app2/public/compromops/${taskId}/ajax-update`;
        
        console.log('URL de actualización:', url);
        
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
            
            // Actualizar el color de la barra si cambió de maquinaria
            if (draggedToNewMaquinaria) {
                // Aquí podrías actualizar el color según el centro
                taskBar.style.backgroundColor = '#4a6cf7'; // Color por defecto
                draggedToNewMaquinaria = false;
            }
        })
        .catch(error => {
            console.error('Error al actualizar:', error);
            showNotification('Error al actualizar la tarea: ' + error.message, true);
        });
    }

    function showNotification(message, isError = false) {
        const notification = document.createElement('div');
        notification.className = `gantt-notification ${isError ? 'gantt-notification-error' : 'gantt-notification-success'}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateY(0)';
            notification.style.opacity = '1';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateY(-20px)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Navegación entre semestres
    prevSemesterBtn.addEventListener('click', function() {
        navigateSemester(-1);
    });
    
    nextSemesterBtn.addEventListener('click', function() {
        navigateSemester(1);
    });
    
    function navigateSemester(direction) {
        currentSemester += direction;
        
        if (currentSemester < 1) {
            currentSemester = 2;
            currentYear--;
        } else if (currentSemester > 2) {
            currentSemester = 1;
            currentYear++;
        }
        
        window.location.href = `{{ route('compromops.index') }}?semester=${currentSemester}&year=${currentYear}`;
    }
    
    const currentSemesterBtn = document.getElementById('currentSemesterBtn');

    currentSemesterBtn.addEventListener('click', function() {
        const today = new Date();
        const todayMonth = today.getMonth() + 1;
        const todayYear = today.getFullYear();
        const todaySemester = todayMonth <= 6 ? 1 : 2;
        
        window.location.href = '{{ route('compromops.index') }}?semester=' + todaySemester + '&year=' + todayYear;
    });
    
    function showConfirmationButtons(taskBar) {
        const originalStartDate = taskBar.getAttribute('data-start-date');
        const originalEndDate = taskBar.getAttribute('data-end-date');
        const originalLeft = taskBar.style.left;
        const originalWidth = taskBar.style.width;
        
        const existingConfirm = taskBar.querySelector('.gantt-confirm-action');
        if (existingConfirm) {
            existingConfirm.remove();
        }
        
        const confirmContainer = document.createElement('div');
        confirmContainer.className = 'gantt-confirm-action';
        
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'gantt-confirm-btn';
        confirmBtn.innerHTML = '✓';
        confirmBtn.title = 'Confirmar cambio';
        
        const commentBtn = document.createElement('button');
        commentBtn.className = 'gantt-comment-btn';
        commentBtn.innerHTML = '💬';
        commentBtn.title = 'Añadir comentario';
        
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'gantt-cancel-btn';
        cancelBtn.innerHTML = '✕';
        cancelBtn.title = 'Cancelar cambio';
        
        confirmBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            updateTaskDatesInDB(taskBar);
            confirmContainer.remove();
        });
        
        commentBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showCommentModal(taskBar);
        });
        
        cancelBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            taskBar.style.left = originalLeft;
            taskBar.style.width = originalWidth;
            
            taskBar.setAttribute('data-start-date', originalStartDate);
            taskBar.setAttribute('data-end-date', originalEndDate);
            
            const startDate = new Date(originalStartDate);
            const endDate = new Date(originalEndDate);
            const datesSpan = taskBar.querySelector('.gantt-task-dates');
            if (datesSpan) {
                datesSpan.textContent = `${startDate.getDate()}/${startDate.getMonth() + 1} - ${endDate.getDate()}/${endDate.getMonth() + 1}`;
            }
            
            confirmContainer.remove();
            showNotification('Cambios cancelados');
        });
        
        confirmContainer.appendChild(confirmBtn);
        confirmContainer.appendChild(commentBtn);
        confirmContainer.appendChild(cancelBtn);
        
        taskBar.appendChild(confirmContainer);
    }

    function showCommentModal(taskBar) {
        const taskId = taskBar.getAttribute('data-task-id');
        
        let commentModal = document.getElementById('ganttCommentModal');
        if (!commentModal) {
            commentModal = document.createElement('div');
            commentModal.id = 'ganttCommentModal';
            commentModal.className = 'gantt-comment-modal';
            
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
            
            document.getElementById('cancelComment').addEventListener('click', function() {
                commentModal.style.display = 'none';
            });
            
            commentModal.addEventListener('click', function(e) {
                if (e.target === commentModal) {
                    commentModal.style.display = 'none';
                }
            });
        }
        
        commentModal.style.display = 'block';
        
        document.getElementById('saveComment').onclick = function() {
            const commentText = document.getElementById('taskComment').value.trim();
            
            if (commentText) {
                saveTaskComment(taskId, commentText, taskBar);
                commentModal.style.display = 'none';
            } else {
                showNotification('Por favor, escribe un comentario', true);
            }
        };
    }

    function saveTaskComment(taskId, comment, taskBar) {
        let baseUrl = window.location.origin;
        const url = baseUrl.includes('localhost') 
            ? `${baseUrl}/compromops/${taskId}/comment`
            : `${baseUrl}/example-app2/public/compromops/${taskId}/comment`;
        
        const formData = new FormData();
        formData.append('comment', comment);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
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
                
                updateTaskDatesInDB(taskBar);
                
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
