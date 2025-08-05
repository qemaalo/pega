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
        
        <!-- Controles de drag mejorados -->
        <div class="gantt-drag-controls">
            <span class="drag-info">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v8"/>
                    <path d="M8 12h8"/>
                </svg>
                <strong>Arrastrar actividades:</strong>
                <span class="drag-mode">Normal: Libre</span> | 
                <span class="drag-mode">Shift + Drag: Solo horizontal</span> | 
                <span class="drag-mode">Alt + Drag: Solo vertical</span>
            </span>
        </div>
        
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
                                <!-- La grilla será renderizada dinámicamente por JavaScript -->
                            </div>
                            <!-- Barras de tareas -->
                            <!-- Las barras de tareas serán renderizadas dinámicamente por JavaScript -->
                        </div>
                        <!-- Área para crear nuevas tareas -->
                        <!--<div id="newTaskArea" class="gantt-new-task-area"></div>-->
<!-- Exportar datos como JSON para JS -->
<script>
    window.ganttData = {
        centros: @json($centros),
        tasks: @json($tasks),
        startDate: '{{ $startDate->format('Y-m-d') }}',
        endDate: '{{ $endDate->format('Y-m-d') }}',
        totalDays: {{ $totalDays }}
    };
</script>

<!-- Script para renderizar la grilla y las barras dinámicamente -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const ganttGrid = document.querySelector('.gantt-grid');
    const ganttBody = document.getElementById('ganttBody');
    const ganttTimeline = document.getElementById('ganttTimeline');
    const sidebar = document.querySelector('.gantt-sidebar');
    const { centros, tasks, startDate, endDate, totalDays } = window.ganttData;

    // Utilidades de fecha
    function addDays(dateStr, days) {
        const d = new Date(dateStr);
        d.setDate(d.getDate() + days);
        return d;
    }
    function formatDate(date) {
        return date.toISOString().slice(0,10);
    }
    function getDayOfWeek(date) {
        return date.getDay();
    }

    // Renderizar grilla
    function renderGrid() {
        ganttGrid.innerHTML = '';
        let maquinariaPositions = {};
        let currentPosition = 0;
        centros.forEach(centro => {
            // Fila header del centro
            const centroRow = document.createElement('div');
            centroRow.className = 'gantt-grid-row gantt-grid-centro-header';
            centroRow.style.height = '54px';
            centroRow.style.minWidth = (totalDays * 50) + 'px';
            centroRow.style.width = (totalDays * 50) + 'px';
            centroRow.setAttribute('data-centro-id', centro.id);
            centroRow.setAttribute('data-type', 'centro');
            // Renderizar celdas para header
            for (let i = 0; i < totalDays; i++) {
                const currentDate = addDays(startDate, i);
                const dayOfWeek = getDayOfWeek(currentDate);
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                const isFirstOfMonth = (currentDate.getDate() === 1);
                const cell = document.createElement('div');
                cell.className = 'gantt-grid-cell';
                if (isWeekend) cell.classList.add('weekend');
                if (isFirstOfMonth) cell.classList.add('first-of-month');
                cell.style.width = '50px';
                cell.style.minWidth = '50px';
                cell.style.height = '100%';
                centroRow.appendChild(cell);
            }
            ganttGrid.appendChild(centroRow);
            currentPosition += 54;

            // Fila por cada maquinaria
            centro.maquinarias.forEach(maquinaria => {
                maquinariaPositions[maquinaria.id] = currentPosition + 15;
                const row = document.createElement('div');
                row.className = 'gantt-grid-row gantt-grid-maquinaria-row';
                row.style.height = '60px';
                row.style.minWidth = (totalDays * 50) + 'px';
                row.style.width = (totalDays * 50) + 'px';
                row.setAttribute('data-maquinaria-id', maquinaria.id);
                row.setAttribute('data-centro-id', centro.id);
                row.setAttribute('data-type', 'maquinaria');
                // Renderizar celdas
                for (let i = 0; i < totalDays; i++) {
                    const currentDate = addDays(startDate, i);
                    const dayOfWeek = getDayOfWeek(currentDate);
                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    const isFirstOfMonth = (currentDate.getDate() === 1);
                    const cell = document.createElement('div');
                    cell.className = 'gantt-grid-cell';
                    if (isWeekend) cell.classList.add('weekend');
                    if (isFirstOfMonth) cell.classList.add('first-of-month');
                    cell.style.width = '50px';
                    cell.style.minWidth = '50px';
                    cell.style.height = '100%';
                    row.appendChild(cell);
                }
                ganttGrid.appendChild(row);
                currentPosition += 60;
            });
        });
        // Guardar posiciones para renderizar barras
        ganttGrid.dataset.maquinariaPositions = JSON.stringify(maquinariaPositions);
    }
    // Sincronizar colapso/expansión del sidebar con la grilla
    function syncCollapseWithGrid() {
        document.querySelectorAll('.gantt-centro-header').forEach(header => {
            header.addEventListener('click', function() {
                const centroId = header.parentElement.getAttribute('data-centro-id');
                const isCollapsed = document.getElementById('centro-maquinarias-' + centroId).classList.contains('collapsed');
                // En la grilla, oculta o muestra las filas de maquinarias de ese centro
                document.querySelectorAll('.gantt-grid-maquinaria-row[data-centro-id="' + centroId + '"]').forEach(row => {
                    row.style.display = isCollapsed ? '' : 'none';
                });
            });
        });
    }

    // Renderizar barras de tareas
    function renderTaskBars() {
        // Elimina barras previas
        const prevBars = ganttTimeline.querySelectorAll('.gantt-task-bar');
        prevBars.forEach(bar => bar.remove());
        const maquinariaPositions = JSON.parse(ganttGrid.dataset.maquinariaPositions || '{}');
        tasks.forEach(task => {
            if (!task.finicio || !task.ftermino) return;
            const taskStartDate = new Date(task.finicio);
            const taskEndDate = new Date(task.ftermino);
            let effectiveStart = taskStartDate;
            let effectiveEnd = taskEndDate;
            const startDateObj = new Date(startDate);
            const endDateObj = new Date(endDate);
            if (effectiveStart < startDateObj) effectiveStart = startDateObj;
            if (effectiveEnd > endDateObj) effectiveEnd = endDateObj;
            const taskStartDay = Math.floor((effectiveStart - startDateObj) / (1000*60*60*24));
            const taskEndDay = Math.floor((effectiveEnd - startDateObj) / (1000*60*60*24));
            let leftPosition = (taskStartDay) * 50;
            let width = (taskEndDay - taskStartDay + 1) * 50;
            if (width < 25) width = 25;
            const rowHeight = 60;
            const barHeight = 45;
            let topPosition = 0;
            if (task.maquinaria_id && maquinariaPositions[task.maquinaria_id]) {
                topPosition = maquinariaPositions[task.maquinaria_id] + (rowHeight / 2) - (barHeight / 2);
            }
            let barColor = '#4a6cf7';
            if (task.maquinaria && task.maquinaria.centro) {
                switch(task.maquinaria.centro.descripcion) {
                    case 'PRENSA': barColor = '#ff6b6b'; break;
                    case 'REVESTIMIENTO': barColor = '#4ecdc4'; break;
                    case 'POLIURETANO': barColor = '#45b7d1'; break;
                    case 'TRAFILA': barColor = '#96ceb4'; break;
                    case 'ANILLOS': barColor = '#feca57'; break;
                }
            }
            const bar = document.createElement('div');
            bar.className = 'gantt-task-bar';
            bar.setAttribute('data-task-id', task.id);
            bar.setAttribute('data-start-date', formatDate(taskStartDate));
            bar.setAttribute('data-end-date', formatDate(taskEndDate));
            bar.setAttribute('data-maquinaria-id', task.maquinaria_id || '');
            bar.setAttribute('data-activo', task.activo);
            bar.style.left = leftPosition + 'px';
            bar.style.width = width + 'px';
            bar.style.top = topPosition + 'px';
            bar.style.backgroundColor = barColor;
            bar.style.position = 'absolute';
            bar.style.height = barHeight + 'px';
            bar.title = `OP ${task.op}-${task.linea} | ${(task.maquinaria && task.maquinaria.nombre) ? task.maquinaria.nombre : 'Sin maquinaria'} | ${formatDate(taskStartDate).slice(5)} - ${formatDate(taskEndDate).slice(5)}`;
            // Contenido
            const content = document.createElement('div');
            content.className = 'gantt-task-content';
            const label = document.createElement('span');
            label.className = 'gantt-task-label';
            label.textContent = `OP ${task.op}-${task.linea}`;
            content.appendChild(label);
            bar.appendChild(content);
            if (task.activo) {
                const resizerLeft = document.createElement('div');
                resizerLeft.className = 'gantt-task-resizer gantt-task-resizer-left';
                bar.appendChild(resizerLeft);
                const resizerRight = document.createElement('div');
                resizerRight.className = 'gantt-task-resizer gantt-task-resizer-right';
                bar.appendChild(resizerRight);
            }
            ganttTimeline.appendChild(bar);
        });
    }

    // Sincronización de scroll vertical con bandera para evitar bucles
    function syncScroll() {
        let isSyncingSidebar = false;
        let isSyncingTimeline = false;

        sidebar.addEventListener('scroll', function() {
            if (isSyncingSidebar) {
                isSyncingSidebar = false;
                return;
            }
            isSyncingTimeline = true;
            ganttGrid.style.transform = `translateY(${-sidebar.scrollTop}px)`;
            ganttTimeline.scrollTop = sidebar.scrollTop;
        });

        ganttTimeline.addEventListener('scroll', function() {
            if (isSyncingTimeline) {
                isSyncingTimeline = false;
                return;
            }
            isSyncingSidebar = true;
            sidebar.scrollTop = ganttTimeline.scrollTop;
            ganttGrid.style.transform = `translateY(${-ganttTimeline.scrollTop}px)`;
        });
    }

    // Inicializar
    renderGrid();
    renderTaskBars();
    syncScroll();
    syncCollapseWithGrid();

    // Calcular y ajustar altura dinámica de grilla y sidebar
    function setDynamicHeights() {
        let totalHeight = 0;
        centros.forEach(centro => {
            totalHeight += 54; // header centro
            totalHeight += centro.maquinarias.length * 60; // filas maquinarias
        });
        // Ajustar altura de la grilla
        ganttGrid.style.height = totalHeight + 'px';
        ganttGrid.style.minHeight = totalHeight + 'px';
        // Ajustar altura del sidebar
        sidebar.style.height = totalHeight + 'px';
        sidebar.style.minHeight = totalHeight + 'px';
    }
    setDynamicHeights();
});
</script>
                    

<style>
    /* Contenedor principal - Adaptable con zoom y altura dinámica */
    .gantt-container {
        width: 100vw;
        max-width: none;
        margin: 0;
        background: #ffffff;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
        height: auto; /* Altura dinámica en lugar de fija */
        min-height: calc(100vh - 60px);
        max-height: 100vh;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-y: auto; /* Permitir scroll vertical */
        overflow-x: hidden;
        /* Adaptación al zoom */
        transform-origin: top left;
        min-width: 100vw;
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
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .gantt-search-form {
        display: flex;
        align-items: center;
    }
    
    /* Controles de drag */
    .gantt-drag-controls {
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .drag-info {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #495057;
    }
    
    .drag-info svg {
        color: #6c757d;
        flex-shrink: 0;
    }
    
    .drag-mode {
        padding: 2px 6px;
        background: #fff;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        font-size: 0.85rem;
        white-space: nowrap;
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
    
    /* Contenedor del Gantt - Adaptable con zoom y scroll mejorado */
    .gantt-chart-container {
        width: 100%;
        flex: 1;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: #ffffff;
        position: relative;
        margin: 0 10px;
        display: flex;
        flex-direction: column;
        min-width: 0;
        height: auto; /* Altura dinámica */
        max-height: none; /* Sin restricción fija */
        /* Zoom responsive */
        transform-origin: top left;
        /* Permitir scroll vertical dinámico */
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Forzar scroll horizontal visible y funcional - mejorado para alturas dinámicas */
    .gantt-scroll-x {
        overflow-x: auto !important;
        overflow-y: hidden;
        width: 100%;
        min-height: 400px;
        height: auto; /* Altura dinámica */
        position: relative;
        background: #fff;
        border-bottom: 1px solid #eee;
        display: block;
        /* Asegurar que el scroll sea siempre visible */
        scrollbar-width: auto !important;
        -ms-overflow-style: auto !important;
        /* Permitir ajuste dinámico */
        flex: 1;
    }
    
    .gantt-scroll-x::-webkit-scrollbar {
        height: 16px !important;
        width: 16px !important;
        display: block !important;
        background: #f1f1f1 !important;
    }
    
    .gantt-scroll-x::-webkit-scrollbar-track {
        background: #f1f1f1 !important;
        border-radius: 8px !important;
    }
    
    .gantt-scroll-x::-webkit-scrollbar-thumb {
        background: #4a6cf7 !important;
        border-radius: 8px !important;
        border: 2px solid #f1f1f1 !important;
    }
    
    .gantt-scroll-x::-webkit-scrollbar-thumb:hover {
        background: #3498db !important;
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
    
    /* Cuerpo del Gantt - Optimizado para scroll sincronizado y altura dinámica */
    .gantt-body {
        display: flex;
        flex: 1;
        overflow: hidden; /* Sin scroll directo en el body */
        position: relative;
        height: auto; /* Altura dinámica */
        min-height: 400px;
        max-height: none; /* Sin restricción máxima */
        width: 100%;
        /* Permitir que se ajuste al contenido y zoom */
    }
    
    /* Sidebar con información de maquinarias - altura dinámica */
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
        height: auto; /* Altura dinámica */
        max-height: none; /* Sin restricción fija */
        /* Se ajustará automáticamente al contenido */
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
        overflow-y: auto;
        background: #ffffff;
        max-height: none;
    }
    
    .gantt-centro-maquinarias.collapsed {
        max-height: 0 !important;
        opacity: 0;
        overflow: hidden !important;
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
        top: 17px;
        left: 0;
        width: 100%;
        z-index: 1;
        min-width: 100%;
        width: 100%;
        transform: translateY(0px);
        transition: transform 0.1s ease-out;
        /* La altura será ajustada dinámicamente por JS */
        height: 400px;
        min-height: 400px;
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
    
    /* Barras de tareas - Aumentadas en tamaño y habilitadas para drag horizontal */
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
        /* Habilitar drag horizontal - propiedades esenciales */
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    
    .gantt-task-bar:hover {
        box-shadow: 0 6px 15px rgba(0,0,0,0.25);
        border-color: #ffffff;
        /* Mantener transformación durante hover */
        transform: translateY(-50%) translateY(-2px);
    }
    
    .gantt-task-bar.dragging {
        opacity: 0.8;
        transform: translateY(-50%) scale(1.05);
        z-index: 1000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.35);
        cursor: grabbing;
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
        /* La altura será ajustada dinámicamente por JS si es necesario */
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
            // También ajustar alturas dinámicas
            adjustDynamicHeights();
        }, 300);
    }
    
    // Función para calcular y ajustar alturas dinámicamente según contenido y zoom
    function adjustDynamicHeights() {
        const ganttContainer = document.querySelector('.gantt-container');
        const ganttBody = document.querySelector('.gantt-body');
        const ganttSidebar = document.querySelector('.gantt-sidebar');
        const ganttChartContainer = document.querySelector('.gantt-chart-container');
        const ganttTimeline = document.getElementById('ganttTimeline');
        
        if (!ganttContainer || !ganttBody || !ganttSidebar || !ganttChartContainer) return;
        
        // Obtener el factor de zoom actual
        const zoomLevel = window.devicePixelRatio || 1;
        const computedZoom = parseFloat(getComputedStyle(document.documentElement).zoom || 1);
        const totalZoom = zoomLevel * computedZoom;
        
        // Calcular altura real del contenido del sidebar
        let contentHeight = 0;
        const centroGroups = ganttSidebar.querySelectorAll('.gantt-centro-group');
        centroGroups.forEach(group => {
            const header = group.querySelector('.gantt-centro-header');
            const maquinarias = group.querySelector('.gantt-centro-maquinarias');
            
            if (header) contentHeight += header.offsetHeight;
            
            if (maquinarias && !maquinarias.classList.contains('collapsed')) {
                const rows = maquinarias.querySelectorAll('.gantt-maquinaria-row');
                rows.forEach(row => {
                    contentHeight += row.offsetHeight;
                });
            }
        });
        
        // Ajustar por zoom - cuando hay zoom, necesitamos más espacio vertical
        const zoomAdjustment = Math.max(1, 1 / totalZoom);
        const adjustedContentHeight = contentHeight * zoomAdjustment;
        
        // Calcular altura disponible del viewport
        const viewportHeight = window.innerHeight;
        const headerHeight = document.querySelector('.gantt-header')?.offsetHeight || 0;
        const searchHeight = document.querySelector('.gantt-search-section')?.offsetHeight || 0;
        const daysHeaderHeight = document.querySelector('.gantt-days-header')?.offsetHeight || 0;
        
        const availableHeight = viewportHeight - headerHeight - searchHeight - daysHeaderHeight - 40; // 40px margin
        const maxHeight = Math.max(400, Math.min(availableHeight, adjustedContentHeight + 100));
        
        // Aplicar alturas dinámicas
        ganttBody.style.height = maxHeight + 'px';
        ganttSidebar.style.height = maxHeight + 'px';
        ganttSidebar.style.maxHeight = maxHeight + 'px';
        
        if (ganttTimeline) {
            ganttTimeline.style.height = maxHeight + 'px';
            ganttTimeline.style.maxHeight = maxHeight + 'px';
        }
        
        // Ajustar el contenedor principal
        ganttChartContainer.style.height = maxHeight + 'px';
        ganttChartContainer.style.maxHeight = maxHeight + 'px';
        
        console.log('Dynamic height adjustment:', {
            zoomLevel,
            computedZoom,
            totalZoom,
            contentHeight,
            adjustedContentHeight,
            maxHeight,
            viewportHeight
        });
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
        
        // Actualizar alturas dinámicamente después de cambios
        adjustDynamicHeights();
        
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
            if (taskBar.getAttribute('data-activo') === "0") {
                return;
            }
            
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
                // Determinar tipo de drag: Shift = solo horizontal, Alt = solo vertical, normal = ambos
                const horizontalOnly = e.shiftKey;
                const verticalOnly = e.altKey;
                
                // Configurar tipo de drag
                this.setAttribute('data-drag-mode', horizontalOnly ? 'horizontal' : (verticalOnly ? 'vertical' : 'both'));
                
                // Añadir clase de drag
                this.classList.add('dragging');
                
                // Iniciar arrastre
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
                
                // Guardar el top original
                draggingTask.setAttribute('data-original-top-px', this.style.top);
                
                // Guardar posición Y inicial para modo horizontal
                draggingTask.setAttribute('data-original-mouse-y', e.clientY);
                
                // Calcular offset vertical entre el mouse y el centro visual de la barra
                let barRect = this.getBoundingClientRect();
                const rowHeight = 60; // GRID_ROW_HEIGHT
                const barHeight = barRect.height;
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
            const dragMode = draggingTask.getAttribute('data-drag-mode') || 'both';
            
            // Calcular nueva posición horizontal
            let newLeft = null;
            if (dragMode === 'horizontal' || dragMode === 'both') {
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
                newLeft = (mouseX - (mouseOffsetPct / 100) * parentW) / parentW * 100;
                newLeft = Math.max(0, Math.min(100 - barWidth, newLeft));
                
                // Aplicar nueva posición horizontal
                draggingTask.style.left = newLeft + '%';
                
                // Actualizar fechas solo en modo horizontal o both
                updateTaskDates(draggingTask, barWidth);
            }
            
            // Calcular nueva posición vertical
            if (dragMode === 'vertical' || dragMode === 'both') {
                // Detectar la fila (maquinaria) bajo el mouse
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
                
                // Calcular nueva posición vertical
                let newTop = null;
                if (targetRow) {
                    const rowRect = targetRow.getBoundingClientRect();
                    const gridRect = targetRow.parentElement.getBoundingClientRect();
                    // Centro de la fila relativo al contenedor de la grilla
                    const rowCenter = (rowRect.top + rowRect.bottom) / 2 - gridRect.top;
                    newTop = rowCenter;
                    
                    // Cambio visual para indicar nueva fila
                    draggingTask.setAttribute('data-maquinaria-id', targetMaquinariaId);
                    draggingTask.style.backgroundColor = '#ff9800';
                    draggingTask.style.opacity = '0.8';
                } else {
                    // Mantener fila original
                    targetMaquinariaId = draggingTask.getAttribute('data-original-maquinaria-id');
                    draggingTask.setAttribute('data-maquinaria-id', targetMaquinariaId);
                    draggingTask.style.backgroundColor = '';
                    draggingTask.style.opacity = '1';
                    
                    // Restaurar posición original
                    let origTop = draggingTask.getAttribute('data-original-top-px');
                    if (origTop !== null && origTop !== undefined) {
                        newTop = parseFloat(origTop);
                    }
                }
                
                if (newTop !== null) {
                    draggingTask.style.top = newTop + 'px';
                    draggingTask.style.transform = 'translateY(-50%)';
                }
            }
            
            // En modo solo horizontal, mantener la fila original
            if (dragMode === 'horizontal') {
                let origTop = draggingTask.getAttribute('data-original-top-px');
                if (origTop !== null && origTop !== undefined) {
                    draggingTask.style.top = origTop;
                    draggingTask.style.transform = 'translateY(-50%)';
                }
                
                // Restaurar color y opacidad originales
                draggingTask.style.backgroundColor = '';
                draggingTask.style.opacity = '1';
            }
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
            const dragMode = draggingTask.getAttribute('data-drag-mode') || 'both';
            
            // Solo procesar cambio de fila si no es modo horizontal puro
            if (dragMode !== 'horizontal') {
                // Detectar la fila real bajo el mouse al soltar, solo contando filas de maquinaria reales
                let targetRow = null;
                let targetMaquinariaId = draggingTask.getAttribute('data-original-maquinaria-id');
                // Solo filas de maquinaria visibles
                const rows = Array.from(document.querySelectorAll('.gantt-maquinaria-row')).filter(row => row.offsetParent !== null);
                const mouseY = e.clientY;
                
                for (let row of rows) {
                    const rect = row.getBoundingClientRect();
                    if (mouseY >= rect.top && mouseY <= rect.bottom) {
                        targetRow = row;
                        targetMaquinariaId = row.getAttribute('data-maquinaria-id');
                        break;
                    }
                }
                
                // Asignar la maquinaria y centrar la barra en la fila
                if (targetRow && typeof window.maquinariaPositions !== 'undefined' && window.maquinariaPositions[targetMaquinariaId] !== undefined) {
                    draggingTask.setAttribute('data-maquinaria-id', targetMaquinariaId);
                    // Centrar perfectamente la barra en la fila
                    const rowHeight = targetRow.offsetHeight || 60;
                    const barHeight = draggingTask.offsetHeight;
                    const offset = (rowHeight - barHeight) / 2;
                    draggingTask.style.top = (window.maquinariaPositions[targetMaquinariaId] + offset) + 'px';
                    draggingTask.style.transform = '';
                } else {
                    // Restaurar maquinaria original si no hay destino válido
                    const originalMaquinaria = draggingTask.getAttribute('data-original-maquinaria-id');
                    draggingTask.setAttribute('data-maquinaria-id', originalMaquinaria);
                    
                    // Restaurar posición vertical original
                    let origTop = draggingTask.getAttribute('data-original-top-px');
                    if (origTop !== null && origTop !== undefined) {
                        draggingTask.style.top = origTop;
                        draggingTask.style.transform = 'translateY(-50%)';
                    }
                }
            } else {
                // En modo horizontal, mantener maquinaria original
                const originalMaquinaria = draggingTask.getAttribute('data-original-maquinaria-id');
                draggingTask.setAttribute('data-maquinaria-id', originalMaquinaria);
                
                // Restaurar posición vertical original
                let origTop = draggingTask.getAttribute('data-original-top-px');
                if (origTop !== null && origTop !== undefined) {
                    draggingTask.style.top = origTop;
                    draggingTask.style.transform = 'translateY(-50%)';
                }
            }
            
            // Limpiar estilos de drag
            draggingTask.style.opacity = '1';
            draggingTask.style.backgroundColor = '';
            draggingTask.classList.remove('dragging');
            
            // Restaurar el ancho original al terminar el drag
            let barWidth = parseFloat(draggingTask.getAttribute('data-original-width'));
            if (!isNaN(barWidth) && barWidth > 0) {
                draggingTask.style.width = barWidth + '%';
            }
            
            // Actualizar fechas y guardar en BD
            updateTaskDates(draggingTask, barWidth);
            updateTaskDatesInDB(draggingTask);
            
            // Limpiar atributos temporales
            draggingTask.removeAttribute('data-original-width');
            draggingTask.removeAttribute('data-drag-mode');
            draggingTask.removeAttribute('data-mouse-offset');
            draggingTask.removeAttribute('data-original-top-px');
            draggingTask.removeAttribute('data-original-mouse-y');
            draggingTask.removeAttribute('data-mouse-offset-y');
            draggingTask.removeAttribute('data-original-maquinaria-id');
            
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
    
    // Event listeners para ajuste dinámico de alturas
    window.addEventListener('resize', function() {
        console.log('Window resized, adjusting heights...');
        setTimeout(adjustDynamicHeights, 100);
    });
    
    // Detectar cambios de zoom del navegador
    window.addEventListener('load', function() {
        adjustDynamicHeights();
    });
    
    // Detectar zoom con media queries (funciona en la mayoría de navegadores)
    const zoomMediaQuery = window.matchMedia('(min-resolution: 1dppx)');
    zoomMediaQuery.addEventListener('change', function() {
        console.log('Zoom changed, adjusting heights...');
        setTimeout(adjustDynamicHeights, 150);
    });
    
    // Observar cambios en el DOM que puedan afectar el layout
    const observer = new MutationObserver(function(mutations) {
        let shouldUpdate = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' || 
                (mutation.type === 'attributes' && 
                 (mutation.attributeName === 'class' || mutation.attributeName === 'style'))) {
                shouldUpdate = true;
            }
        });
        if (shouldUpdate) {
            setTimeout(adjustDynamicHeights, 50);
        }
    });
    
    observer.observe(document.querySelector('.gantt-sidebar'), {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    // Ajuste inicial
    setTimeout(adjustDynamicHeights, 500);
});
</script>

@endsection
