@extends('layouts.app')

@section('content')
<div class="gantt-container">
    <div class="gantt-header">
        <div class="gantt-header-left">
            <h1 class="gantt-title">Carta Gantt de Tareas</h1>
        </div>
        
        <div class="gantt-header-center">
            <div class="gantt-month-navigation">
                <button id="prevSemester" class="gantt-nav-btn gantt-nav-btn-prev">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"></path>
                    </svg>
                    <span>Ant</span>
                </button>
                
                <div class="gantt-month-display">
                    <span id="currentSemesterDisplay" class="gantt-current-month">{{ $dateString }}</span>
                </div>
                
                <button id="nextSemester" class="gantt-nav-btn gantt-nav-btn-next">
                    <span>Sig</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"></path>
                    </svg>
                </button>
                
                <button id="currentSemesterBtn" class="gantt-nav-btn-today">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Hoy
                </button>
            </div>
        </div>
        
        <div class="gantt-header-right">
            <form action="{{ route('compromops.index') }}" method="GET" class="gantt-search-form">
                <div class="search-container">
                    <input type="text" name="search_op" placeholder="Buscar por OP..." class="gantt-search-input" value="{{ request()->search_op ?? '' }}">
                    <button type="submit" class="gantt-search-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="gantt-chart-container">
        <!-- Headers row - ambos headers a la misma altura -->
        <div class="gantt-headers-row" style="display: flex; position: sticky; top: 0; z-index: 300;">
            <!-- Header del sidebar -->
            <div class="gantt-sidebar-header">Maquinarias</div>
            <!-- Header del timeline -->
            <div class="gantt-timeline-header" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; display: flex;">
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
        </div>
        
        <!-- Body row con sidebar estático y grilla con scroll -->
        <div class="gantt-body" style="display: flex; flex: 1;">
            <!-- Sidebar estático -->
            <div class="gantt-sidebar" id="ganttSidebar">
                <div class="gantt-sidebar-content">
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
            
            <!-- Timeline con scroll -->
            <div class="gantt-timeline-container" id="ganttTimelineContainer" style="flex: 1;">
                <div class="gantt-timeline" id="ganttTimeline" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; overflow: hidden;">
                    <!-- Grilla directamente sin contenedor adicional -->
                    <div class="gantt-grid" style="min-width: {{ $totalDays * 50 }}px; width: {{ $totalDays * 50 }}px; position: relative; overflow: hidden;">
                        <!-- La grilla será renderizada dinámicamente por JavaScript -->
                    </div>
                    <!-- Barras de tareas -->
                    <!-- Las barras de tareas serán renderizadas dinámicamente por JavaScript -->
                </div>
            </div>
        </div>
    </div>
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
    const ganttBody = document.querySelector('.gantt-body');
    const ganttTimelineContainer = document.getElementById('ganttTimelineContainer');
    const ganttTimeline = document.getElementById('ganttTimeline');
    const sidebar = document.getElementById('ganttSidebar');
    const { centros, tasks, startDate, endDate, totalDays } = window.ganttData;

    // FUNCIÓN GLOBAL: Configurar eventos de barras de tareas  
    window.setupTaskBarEvents = function(taskBar) {
        // Verificar si la tarea está inactiva
        if (taskBar.getAttribute('data-activo') === "0") {
            return;
        }

        // Agregar eventos básicos de arrastre
        taskBar.addEventListener('mousedown', function(e) {
            if (taskBar.getAttribute('data-activo') === "0") {
                return;
            }
            
            console.log('Iniciando arrastre de barra:', taskBar.getAttribute('data-task-id'));
            taskBar.classList.add('dragging');
            
            // Prevenir selección de texto
            e.preventDefault();
            
            // Configurar arrastre básico (se puede expandir más tarde)
            document.addEventListener('mouseup', function handler() {
                taskBar.classList.remove('dragging');
                console.log('Arrastre terminado');
                document.removeEventListener('mouseup', handler);
            });
        });

        // Eventos para redimensionadores si existen
        const resizers = taskBar.querySelectorAll('.gantt-task-resizer');
        resizers.forEach(resizer => {
            resizer.addEventListener('mousedown', function(e) {
                e.stopPropagation(); // Evitar que se active el arrastre de la barra
                console.log('Iniciando redimensionado');
            });
        });
    };

    // DEBUGGING: Verificar datos completos
    console.log('=== VERIFICACIÓN DE DATOS COMPLETOS ===');
    console.log('Total centros:', centros.length);
    let totalMaquinarias = 0;
    centros.forEach((centro, index) => {
        console.log(`Centro ${index + 1}: ${centro.descripcion} - ${centro.maquinarias.length} maquinarias`);
        centro.maquinarias.forEach((maq, maqIndex) => {
            totalMaquinarias++;
            console.log(`  ${totalMaquinarias}. ${maq.nombre} (ID: ${maq.id})`);
        });
    });
    console.log('Total maquinarias:', totalMaquinarias);
    console.log('=== FIN VERIFICACIÓN ===');

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

    // Renderizar grilla con alineación perfecta
    function renderGrid() {
        // Verificar que el sidebar esté cargado
        const sidebarElement = document.getElementById('ganttSidebar');
        if (!sidebarElement) {
            console.error('Sidebar no encontrado, reintentando en 100ms...');
            setTimeout(renderGrid, 100);
            return;
        }
        
        ganttGrid.innerHTML = '';
        let maquinariaPositions = {};
        let currentPosition = 0; // Comenzar desde 0 para alineación perfecta
        
        // ALTURA UNIFORME para TODOS los elementos (headers + maquinarias)
        const UNIFORM_HEIGHT = 60; // Todos los elementos tendrán 60px
        
        let rowIndex = 0;
        
        console.log('=== INICIANDO RENDERIZADO DE GRILLA ===');
        
        // CRÍTICO: Renderizar filas para CADA elemento visible del sidebar
        document.querySelectorAll('#ganttSidebar .gantt-centro-group').forEach(centroGroup => {
            const centroId = centroGroup.getAttribute('data-centro-id');
            const centro = ganttData.centros.find(c => c.id == centroId);
            
            if (!centro) {
                console.warn('Centro no encontrado:', centroId);
                return;
            }
            
            // 1. FILA HEADER DEL CENTRO (SIEMPRE visible)
            console.log(`Renderizando header centro: ${centro.descripcion}`);
            const centroRow = document.createElement('div');
            centroRow.className = 'gantt-grid-row gantt-grid-centro-header';
            centroRow.style.height = UNIFORM_HEIGHT + 'px';
            centroRow.style.minHeight = UNIFORM_HEIGHT + 'px';
            centroRow.style.maxHeight = UNIFORM_HEIGHT + 'px';
            centroRow.style.minWidth = (totalDays * 50) + 'px';
            centroRow.style.width = (totalDays * 50) + 'px';
            centroRow.setAttribute('data-centro-id', centro.id);
            centroRow.setAttribute('data-type', 'centro');
            centroRow.setAttribute('data-row-index', rowIndex);
            
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
            currentPosition += UNIFORM_HEIGHT;
            rowIndex++;

            // 2. FILAS DE MAQUINARIAS - Renderizar TODAS las maquinarias visibles
            const maquinariasContainer = centroGroup.querySelector('.gantt-centro-maquinarias');
            const isCollapsed = maquinariasContainer ? maquinariasContainer.classList.contains('collapsed') : false;
            
            console.log(`Centro ${centro.descripcion} - Colapsado: ${isCollapsed}`);
            
            if (!isCollapsed && centro.maquinarias && centro.maquinarias.length > 0) {
                // Renderizar TODAS las maquinarias del centro (no solo las visibles del sidebar)
                centro.maquinarias.forEach((maquinaria, maqIndex) => {
                    console.log(`  Renderizando maquinaria ${maqIndex + 1}/${centro.maquinarias.length}: ${maquinaria.nombre}`);
                    
                    // Calcular posición exacta para las barras de tareas - usar currentPosition actual
                    maquinariaPositions[maquinaria.id] = currentPosition;
                    
                    const row = document.createElement('div');
                    row.className = 'gantt-grid-row gantt-grid-maquinaria-row';
                    row.style.height = UNIFORM_HEIGHT + 'px';
                    row.style.minHeight = UNIFORM_HEIGHT + 'px';
                    row.style.maxHeight = UNIFORM_HEIGHT + 'px';
                    row.style.minWidth = (totalDays * 50) + 'px';
                    row.style.width = (totalDays * 50) + 'px';
                    row.setAttribute('data-maquinaria-id', maquinaria.id);
                    row.setAttribute('data-centro-id', centro.id);
                    row.setAttribute('data-type', 'maquinaria');
                    row.setAttribute('data-row-index', rowIndex);
                    
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
                    currentPosition += UNIFORM_HEIGHT;
                    rowIndex++;
                });
            }
        });
        
        // Aplicar altura total exacta a la grilla + buffer de seguridad
        const finalHeight = currentPosition + 10; // 10px buffer para asegurar visibilidad completa
        ganttGrid.style.height = finalHeight + 'px';
        ganttGrid.style.minHeight = finalHeight + 'px';
        
        // Asegurar que el timeline tenga la altura adecuada
        ganttTimeline.style.height = finalHeight + 'px';
        ganttTimeline.style.minHeight = finalHeight + 'px';
        
        // Guardar posiciones para renderizar barras
        ganttGrid.dataset.maquinariaPositions = JSON.stringify(maquinariaPositions);
        
        console.log('=== RENDERIZADO COMPLETADO ===');
        console.log('Filas renderizadas:', rowIndex);
        console.log('Maquinarias con posiciones:', Object.keys(maquinariaPositions).length);
        console.log('Altura final:', finalHeight + 'px');
        console.log('Posiciones guardadas:', maquinariaPositions);
    }
    
    // Sincronizar colapso/expansión del sidebar con la grilla
    function syncCollapseWithGrid() {
        // Esta función ahora se encarga de re-renderizar la grilla cada vez que cambia el estado
        // Ya no necesitamos manipular filas individuales, sino re-renderizar todo
        console.log('Sincronizando grilla con sidebar...');
        renderGrid(); // Re-renderizar toda la grilla
        renderTaskBars(); // Re-renderizar las barras de tareas
        
        // Verificar alineación después de sincronizar
        setTimeout(() => {
            ensurePerfectAlignment();
        }, 50);
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
            const rowHeight = 60; // UNIFORM_HEIGHT
            const barHeight = 45;
            let topPosition = 0;
            if (task.maquinaria_id && maquinariaPositions[task.maquinaria_id]) {
                // Centrar la barra en la fila de la maquinaria
                topPosition = maquinariaPositions[task.maquinaria_id] + (rowHeight - barHeight) / 2;
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
        
        // CRÍTICO: Configurar eventos de interactividad para las barras recién creadas
        console.log('Configurando eventos de interactividad para barras...');
        document.querySelectorAll('.gantt-task-bar').forEach(bar => {
            if (window.setupTaskBarEvents) {
                window.setupTaskBarEvents(bar);
            }
        });
        console.log('Eventos de barras configurados');
    }

    // FUNCIÓN PARA VERIFICAR ALINEACIÓN PERFECTA
    function ensurePerfectAlignment() {
        console.log('🎯 Verificando alineación perfecta...');
        
        const EXPECTED_HEIGHT = 60;
        let alignmentIssues = 0;
        
        // Verificar elementos del sidebar
        document.querySelectorAll('.gantt-centro-header, .gantt-maquinaria-row').forEach((el, index) => {
            const height = el.offsetHeight;
            if (height !== EXPECTED_HEIGHT) {
                console.warn(`❌ Sidebar elemento ${index}: altura ${height}px (esperaba ${EXPECTED_HEIGHT}px)`);
                el.style.height = EXPECTED_HEIGHT + 'px';
                el.style.minHeight = EXPECTED_HEIGHT + 'px';
                el.style.maxHeight = EXPECTED_HEIGHT + 'px';
                alignmentIssues++;
            }
        });
        
        // Verificar elementos de la grilla
        document.querySelectorAll('.gantt-grid-centro-header, .gantt-grid-maquinaria-row').forEach((el, index) => {
            const height = el.offsetHeight;
            if (height !== EXPECTED_HEIGHT) {
                console.warn(`❌ Grid elemento ${index}: altura ${height}px (esperaba ${EXPECTED_HEIGHT}px)`);
                el.style.height = EXPECTED_HEIGHT + 'px';
                el.style.minHeight = EXPECTED_HEIGHT + 'px';
                el.style.maxHeight = EXPECTED_HEIGHT + 'px';
                alignmentIssues++;
            }
        });
        
        if (alignmentIssues === 0) {
            console.log('✅ Alineación perfecta confirmada - todos los elementos tienen 60px');
        } else {
            console.log(`⚠️  Corregidos ${alignmentIssues} problemas de alineación`);
        }
    }

    // SISTEMA UNIFICADO DE SCROLL SINCRONIZADO
    function syncScroll() {
        console.log('Configurando sistema de scroll sincronizado...');
        
        // Verificar que los elementos existan
        const sidebarContent = sidebar.querySelector('.gantt-sidebar-content');
        const timelineHeader = document.querySelector('.gantt-timeline-header');
        
        console.log('Elementos para scroll:', {
            ganttTimelineContainer: !!ganttTimelineContainer,
            sidebar: !!sidebar,
            sidebarContent: !!sidebarContent,
            timelineHeader: !!timelineHeader
        });
        
        if (!ganttTimelineContainer || !sidebar || !sidebarContent) {
            console.error('❌ No se pudieron encontrar elementos necesarios para el scroll');
            return;
        }
        
        // SCROLL VERTICAL: Sincronizar sidebar con timeline
        ganttTimelineContainer.addEventListener('scroll', function() {
            const scrollTop = ganttTimelineContainer.scrollTop;
            const scrollLeft = ganttTimelineContainer.scrollLeft;
            
            // Sincronizar contenido del sidebar verticalmente
            if (sidebarContent) {
                sidebarContent.style.transform = `translateY(${-scrollTop}px)`;
            }
            
            // SCROLL HORIZONTAL: Sincronizar header del timeline
            if (timelineHeader) {
                timelineHeader.style.transform = `translateX(${-scrollLeft}px)`;
            }
            
            // Debug ocasional
            if (Math.random() < 0.01) { // 1% de las veces
                console.log('Scroll sincronizado - Top:', scrollTop, 'Left:', scrollLeft);
            }
        });
        
        // SCROLL HORIZONTAL ADICIONAL: Si alguien hace scroll en el header, sincronizar con el timeline
        if (timelineHeader) {
            timelineHeader.addEventListener('scroll', function() {
                if (Math.abs(ganttTimelineContainer.scrollLeft - this.scrollLeft) > 1) {
                    ganttTimelineContainer.scrollLeft = this.scrollLeft;
                }
            });
        }
        
        console.log('✅ Sistema de scroll sincronizado configurado correctamente');
        window.ganttScrollConfigured = true; // Marcar como configurado
    }

    // Verificar y corregir alineación después del renderizado
    function verifyAlignment() {
        const sidebarGroups = sidebar.querySelectorAll('.gantt-centro-group');
        const gridRows = ganttGrid.querySelectorAll('.gantt-grid-row');
        
        console.log('Verificando alineación:');
        console.log('Grupos en sidebar:', sidebarGroups.length);
        console.log('Filas en grilla:', gridRows.length);
        
        // Verificar que cada grupo del sidebar tenga su fila correspondiente en la grilla
        let sidebarRowIndex = 0;
        sidebarGroups.forEach((group, groupIndex) => {
            const centroHeader = group.querySelector('.gantt-centro-header');
            const maquinarias = group.querySelectorAll('.gantt-maquinaria-row');
            
            console.log(`Grupo ${groupIndex}: 1 header + ${maquinarias.length} maquinarias`);
            
            // Verificar header del centro
            if (gridRows[sidebarRowIndex]) {
                console.log(`Fila grilla ${sidebarRowIndex}: ${gridRows[sidebarRowIndex].getAttribute('data-type')}`);
            }
            sidebarRowIndex++;
            
            // Verificar maquinarias
            maquinarias.forEach((maq, maqIndex) => {
                if (gridRows[sidebarRowIndex]) {
                    console.log(`Fila grilla ${sidebarRowIndex}: ${gridRows[sidebarRowIndex].getAttribute('data-type')} (maq-id: ${gridRows[sidebarRowIndex].getAttribute('data-maquinaria-id')})`);
                }
                sidebarRowIndex++;
            });
        });
    }

    // Funciones de utilidad inicializadas - las llamadas principales están al final del script
    
    // Verificar alineación después del renderizado
    setTimeout(() => {
        verifyAlignment();
    }, 100);

    // Calcular y ajustar altura dinámica de grilla y sidebar para mantener sincronización
    function setDynamicHeights() {
        let totalHeight = 0;
        centros.forEach(centro => {
            totalHeight += 60; // header centro - CORREGIDO a 60px
            totalHeight += centro.maquinarias.length * 60; // filas maquinarias
        });
        
        // Agregar buffer de seguridad para asegurar visibilidad completa
        totalHeight += 10;
        
        // Ajustar altura de la grilla
        ganttGrid.style.height = totalHeight + 'px';
        ganttGrid.style.minHeight = totalHeight + 'px';
        
        // Ajustar altura del contenido del sidebar para que coincida con la grilla
        const sidebarContent = sidebar.querySelector('.gantt-sidebar-content');
        if (sidebarContent) {
            sidebarContent.style.height = totalHeight + 'px';
            sidebarContent.style.minHeight = totalHeight + 'px';
        }
        
        // CORRECCIÓN: Altura ajustada para funcionar con scroll sincronizado
        ganttTimelineContainer.style.height = 'calc(100vh - 180px)'; /* Coordinado con CSS */
        ganttTimelineContainer.style.maxHeight = 'calc(100vh - 180px)';
        ganttTimelineContainer.style.overflowY = 'auto'; // Permitir scroll vertical
        
        // Asegurar que el timeline tenga la altura completa para scroll
        ganttTimeline.style.height = totalHeight + 'px';
        ganttTimeline.style.minHeight = totalHeight + 'px';
        
        console.log('Altura total configurada:', totalHeight + 'px');
    }
    
    // INICIALIZACIÓN PRINCIPAL
    console.log('=== INICIANDO SISTEMA GANTT ===');
    renderGrid();
    renderTaskBars();
    setDynamicHeights();
    
    // VERIFICAR ALINEACIÓN PERFECTA
    setTimeout(() => {
        ensurePerfectAlignment(); // Verificar que todos los elementos estén alineados
    }, 50);
    
    // CONFIGURAR SCROLL DESPUÉS DE QUE TODO ESTÉ RENDERIZADO
    setTimeout(() => {
        syncScroll(); // CRÍTICO: Configurar el sistema de scroll sincronizado
        console.log('✅ Scroll sincronizado configurado');
    }, 100);
    
    // CONFIGURAR EVENTOS DE BARRAS DESPUÉS DE LA INICIALIZACIÓN
    setTimeout(() => {
        console.log('Configuración final de eventos...');
        document.querySelectorAll('.gantt-task-bar').forEach(bar => {
            if (window.setupTaskBarEvents) {
                window.setupTaskBarEvents(bar);
            }
        });
        console.log('✅ Sistema de Gantt completamente inicializado');
    }, 500);
});
</script>
                    
<style>
    /* Eliminar todos los scrolls excepto los de la grilla */
    html, body {
        overflow: hidden !important;
        height: 100vh;
        max-height: 100vh;
    }
    
    /* Eliminar scrollbars de todos los elementos por defecto */
    * {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    *::-webkit-scrollbar {
        display: none;
    }
    
    /* Solo permitir scrollbars en el timeline container */
    .gantt-timeline-container {
        scrollbar-width: auto !important;
        -ms-overflow-style: auto !important;
    }
    
    .gantt-timeline-container::-webkit-scrollbar {
        display: block !important;
        height: 16px;
        width: 16px;
        background: #f1f1f1;
    }
    
    .gantt-timeline-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 8px;
    }
    
    .gantt-timeline-container::-webkit-scrollbar-thumb {
        background: #4a6cf7;
        border-radius: 8px;
        border: 2px solid #f1f1f1;
    }
    
    .gantt-timeline-container::-webkit-scrollbar-thumb:hover {
        background: #3498db;
    }
    
    /* Contenedor principal - optimizado para mejor aprovechamiento del espacio */
    .gantt-container {
        width: 100vw;
        max-width: none;
        margin: 0;
        background: #ffffff;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
        height: 100vh;
        min-height: 100vh;
        max-height: 100vh;
        display: flex;
        flex-direction: column;
        position: fixed; /* Fixed para asegurar que use toda la pantalla */
        top: 0;
        left: 0;
        overflow: hidden;
        transform-origin: top left;
        min-width: 100vw;
        z-index: 1000; /* Por encima de todo */
    }

    /* Encabezado - nuevo layout con tres secciones */
    .gantt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0;
        padding: 8px 25px; /* Reducido de 12px a 8px */
        border-bottom: 1px solid #dee2e6; /* Más sutil */
        background: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 300;
        flex-shrink: 0;
        min-height: 50px; /* Altura mínima controlada */
        max-height: 50px; /* Altura máxima controlada */
    }
    
    .gantt-header-left {
        flex: 0 0 auto;
        min-width: 200px;
    }
    
    .gantt-header-center {
        flex: 1;
        display: flex;
        justify-content: center;
        max-width: 600px;
    }
    
    .gantt-header-right {
        flex: 0 0 auto;
        min-width: 280px;
        display: flex;
        justify-content: flex-end;
    }
    
    .gantt-title {
        font-size: 1.6rem; /* Reducido de 2.2rem */
        color: #2c3e50;
        margin: 0;
        font-weight: 600; /* Reducido de 700 */
        letter-spacing: -0.3px;
    }
    
    /* === BOTONES OPTIMIZADOS === */
    .gantt-btn, .gantt-btn-secondary {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .gantt-btn {
        background: #3a3a3a;
        color: #fff;
    }
    
    .gantt-btn:hover {
        background: #222;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
    
    .gantt-btn-secondary {
        background: #f5f5f5;
        color: #555;
    }
    
    .gantt-btn-secondary:hover {
        background: #e8e8e8;
    }
    
    /* Sección de búsqueda - reducida para mejor aprovechamiento del espacio */
    .gantt-search-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0;
        padding: 12px 20px;
        background: #fff;
        border-bottom: 2px solid #e0e0e0;
        position: sticky;
        top: 82px;
        z-index: 290;
        flex-shrink: 0;
        flex-wrap: wrap;
        gap: 15px;
        overflow: hidden;
    }
    
    .gantt-search-form {
        display: flex;
        align-items: center;
    }
    
    .search-container {
        display: flex;
        align-items: center;
        border-radius: 16px; /* Más alargado */
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
        transition: all 0.2s ease;
        height: 32px; /* Altura fija y compacta */
        box-sizing: border-box;
    }
    
    .search-container:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }
    
    .gantt-search-input {
        border: none;
        outline: none;
        padding: 6px 10px; /* Más compacto */
        font-size: 0.8rem; /* Más pequeño */
        background: transparent;
        color: #495057;
        width: 160px; /* Más estrecho */
        font-weight: 400;
        height: 100%;
        box-sizing: border-box;
    }
    
    .gantt-search-input::placeholder {
        color: #9ca3af;
        font-style: normal;
        font-size: 0.75rem;
    }
    
    .gantt-search-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 6px 8px; /* Más compacto */
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-width: 28px;
    }
    
    .gantt-search-btn:hover {
        background: #5a6268;
    }
    
    .gantt-search-btn svg {
        width: 14px;
        height: 14px;
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
    
    /* Navegación del mes - En header */
    .gantt-month-navigation {
        display: flex;
        align-items: center;
        gap: 4px; /* Reducido de 8px */
        background: #ffffff;
        padding: 4px 12px; /* Reducido de 8px 16px */
        border-radius: 20px; /* Más alargado */
        border: 1px solid #e0e0e0;
        height: 32px; /* Altura fija y compacta */
        box-sizing: border-box;
    }

    .gantt-nav-btn {
        display: flex;
        align-items: center;
        gap: 3px; /* Reducido */
        background: #6c757d;
        color: white;
        border: none;
        padding: 4px 8px; /* Muy compacto */
        border-radius: 12px; /* Más alargado */
        font-size: 0.75rem; /* Más pequeño */
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 24px; /* Altura fija */
        box-sizing: border-box;
        min-width: 60px; /* Ancho mínimo para mantener proporción alargada */
    }

    .gantt-nav-btn:hover {
        background: #5a6268;
        transform: none; /* Sin efectos de movimiento */
    }

    /* Botón mes anterior */
    .gantt-nav-btn-prev {
        background: #dc3545;
    }

    .gantt-nav-btn-prev:hover {
        background: #c82333;
    }

    /* Botón mes siguiente */
    .gantt-nav-btn-next {
        background: #28a745;
    }

    .gantt-nav-btn-next:hover {
        background: #218838;
    }

    /* Visualización del mes actual */
    .gantt-month-display {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 8px; /* Reducido de 12px */
        min-width: 140px; /* Reducido de 180px */
        text-align: center;
        height: 24px; /* Altura fija */
        justify-content: center;
    }

    .gantt-current-month {
        font-weight: 600;
        font-size: 0.85rem; /* Reducido de 1.1rem */
        color: #2c3e50;
        margin: 0;
        line-height: 1;
    }
    
    .gantt-date-range {
        display: none; /* Ocultamos las fechas para ganar espacio */
    }

    .gantt-nav-btn-today {
        display: flex;
        align-items: center;
        gap: 3px;
        background: #17a2b8;
        color: white;
        border: none;
        padding: 4px 8px; /* Compacto */
        border-radius: 12px; /* Más alargado */
        font-size: 0.75rem; /* Más pequeño */
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 24px; /* Altura fija */
        box-sizing: border-box;
        min-width: 50px; /* Ancho mínimo */
    }
    
    .gantt-nav-btn-today:hover {
        background: #138496;
        transform: none; /* Sin efectos de movimiento */
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
    
    /* === CONTENEDOR Y HEADERS OPTIMIZADOS === */
    
    /* Contenedor del Gantt - usar máxima altura disponible */
    .gantt-chart-container {
        width: 100%;
        flex: 1;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: #ffffff;
        position: relative;
        margin: 0 10px 10px 10px;
        display: flex;
        flex-direction: column;
        min-width: 0;
        height: calc(100vh - 120px); /* Altura ajustada para el header del Gantt */
        max-height: calc(100vh - 120px);
        transform-origin: top left;
        overflow: hidden;
    }
    
    /* Headers unificados - altura estándar de 60px */
    .gantt-headers-row {
        display: flex;
        position: sticky;
        top: 0;
        z-index: 300;
        background: #fff;
        border-bottom: 2px solid #2c3e50;
    }
    
    /* === HEADERS OPTIMIZADOS === */
    .gantt-sidebar-header,
    .gantt-timeline-header {
        height: 60px;
        min-height: 60px;
        max-height: 60px;
        border-bottom: 1px solid #2c3e50;
        flex-shrink: 0;
    }
    
    .gantt-sidebar-header {
        width: 300px;
        min-width: 300px;
        background: #34495e;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        border-right: 3px solid #2c3e50;
        z-index: 290;
        box-sizing: border-box;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin: 0;
        padding: 0 20px;
    }
    
    .gantt-timeline-header {
        display: flex;
        min-width: {{ $totalDays * 50 }}px;
        width: {{ $totalDays * 50 }}px;
        background: #ecf0f1;
        overflow: hidden;
        z-index: 275;
        scroll-behavior: smooth;
        will-change: transform; /* Optimizar para transforms */
        transition: none; /* Sin transiciones para scroll suave */
    }
    
    /* === COLUMNAS DE DÍAS OPTIMIZADAS === */
    .gantt-day-column {
        width: 50px;
        min-width: 50px;
        height: 60px;
        border-right: 1px solid #bdc3c7;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #ecf0f1;
        transition: background-color 0.2s ease;
        position: relative;
        flex-shrink: 0;
        box-sizing: border-box;
    }
    
    .gantt-day-column:hover {
        background: #d5dbdb;
    }
    
    .gantt-day-column:last-child {
        border-right: none;
    }
    
    .gantt-day-column.weekend {
        background: #f39c12;
        color: white;
    }
    
    .gantt-day-column.weekend:hover {
        background: #e67e22;
    }
    
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
    
    /* === CUERPO DEL GANTT OPTIMIZADO === */
    .gantt-body {
        display: flex;
        flex: 1;
        position: relative;
        overflow: hidden;
        height: calc(100vh - 180px); /* Coordinado con el contenedor */
        min-height: 400px;
        max-height: calc(100vh - 180px);
        width: 100%;
        align-items: flex-start;
        margin: 0;
        padding: 0;
        border: 0;
    }
    
    .gantt-sidebar {
        width: 300px;
        min-width: 300px;
        background: #f8f9fa;
        border-right: 3px solid #2c3e50;
        overflow: hidden;
        position: relative;
        z-index: 300;
        flex-shrink: 0;
        height: 100%;
        max-height: 100%;
        transform-origin: top left;
        display: flex;
        flex-direction: column;
        will-change: transform;
    }
    
    .gantt-sidebar-content {
        width: 100%;
        height: auto;
        display: flex;
        flex-direction: column;
        will-change: transform;
        transition: none;
        transform-origin: top left;
        margin: 0;
        padding: 0;
        border: 0;
        gap: 0;
        align-self: flex-start;
        position: relative;
        top: 0;
        left: 0;
    }
    
    .gantt-centro-group {
        margin-bottom: 0; /* Sin margen para evitar espacios entre centros */
        margin: 0; /* Sin márgenes en ninguna dirección */
        padding: 0; /* Sin padding que cause espacios */
        border: none; /* Sin bordes que añadan tamaño */
        /* Asegurar que no haya espacios entre grupos */
        display: block;
    }
    
    .gantt-centro-header {
        display: flex;
        justify-content: flex-start; /* Alinear a la izquierda pero con espacio */
        align-items: center;
        padding: 10px 40px; /* Más padding izquierdo para alejar del borde */
        background: #34495e;
        color: white;
        margin-bottom: 0; /* Sin margen inferior */
        cursor: pointer;
        transition: background-color 0.3s ease;
        user-select: none;
        /* ALTURA FIJA IGUAL que maquinarias para alineación perfecta */
        height: 60px;
        min-height: 60px;
        max-height: 60px;
        box-sizing: border-box;
        /* Eliminar cualquier margen adicional */
        margin: 0;
        border: none; /* Sin bordes que causen separación */
        text-align: left; /* Alinear texto a la izquierda */
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
        overflow: hidden; /* Sin scroll */
        background: #ffffff;
        max-height: none;
        /* Asegurar alineación perfecta SIN gaps */
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        gap: 0; /* Sin espacios entre elementos hijos */
        border: none;
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
        padding: 8px 50px; /* Más padding izquierdo para indentar las maquinarias */
        transition: all 0.2s ease;
        cursor: pointer;
        background: #ffffff;
        /* ALTURA FIJA para alineación perfecta con grilla */
        height: 60px; 
        min-height: 60px;
        max-height: 60px;
        display: flex;
        align-items: center;
        justify-content: flex-start; /* Alinear a la izquierda con espacio */
        box-sizing: border-box;
        /* Asegurar que no se deforme con zoom */
        flex-shrink: 0;
        /* Eliminar cualquier margen que cause desalineación */
        margin: 0;
        margin-bottom: 0;
        border: none; /* Sin border que afecte el tamaño */
        /* Línea inferior sutil usando box-shadow para no afectar tamaño */
        box-shadow: inset 0 -1px 0 0 #dee2e6 !important;
        border-bottom: 1px solid #e9ecef; /* Línea adicional para asegurar visibilidad */
        text-align: left; /* Alinear el texto a la izquierda */
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
        text-align: left; /* Alinear texto a la izquierda */
        width: 100%; /* Ocupar todo el ancho disponible */
    }
    
    .gantt-empty-sidebar {
        padding: 30px 20px;
        color: #999;
        font-style: italic;
        text-align: center;
        font-size: 1.1rem;
    }
    
    /* Contenedor del Timeline - alineación perfecta */
    .gantt-timeline-container {
        flex: 1;
        position: relative;
        /* SCROLL PRINCIPAL - tanto vertical como horizontal */
        overflow: auto;
        background: #fafafa;
        border-left: 2px solid #ddd;
        /* Asegurar alineación perfecta */
        margin: 0;
        padding: 0;
        border-top: 0;
        border-bottom: 0;
        border-right: 0;
    }
    
    /* Timeline - alineación perfecta con sidebar */
    .gantt-timeline {
        position: relative;
        /* Sin scroll - el scroll está en el container padre */
        overflow: hidden !important;
        background: #fafafa;
        min-width: calc({{ $totalDays }} * 50px);
        width: calc({{ $totalDays }} * 50px);
        height: auto;
        min-height: 100%;
        /* Asegurar alineación perfecta */
        margin: 0;
        padding: 0;
        border: 0;
        z-index: 10; /* Entre grilla (1) y barras (50) */
    }
    
    /* Grid de fondo - alineación perfecta con sidebar sin headers */
    .gantt-grid {
        position: absolute;
        top: 0;
        left: 0;
        width: calc({{ $totalDays }} * 50px);
        min-width: calc({{ $totalDays }} * 50px);
        z-index: 1; /* Debajo del sidebar */
        /* Altura dinámica que será calculada por JS */
        height: auto;
        min-height: 100%;
        /* Sin transformaciones - se mueve con el scroll del body */
        transform: none;
        transition: none;
        /* Asegurar alineación perfecta desde el inicio */
        margin: 0;
        padding: 0;
        border: 0;
        /* Iniciar desde el mismo punto que el sidebar */
        vertical-align: top;
        /* CRÍTICO: Sin scroll interno */
        overflow: hidden !important;
        pointer-events: auto; /* Permitir interacción con las celdas */
        /* FORZAR que comience exactamente desde arriba */
        line-height: 0;
        font-size: 0;
    }
    
    .gantt-grid-row {
        position: relative;
        left: 0;
        width: 100%;
        height: auto; /* Altura automática, se define específicamente en cada tipo */
        display: flex;
        border-bottom: 1px solid #e0e0e0;
        /* Ancho completo para scroll horizontal */
        min-width: calc({{ $totalDays }} * 50px);
        width: calc({{ $totalDays }} * 50px);
        /* Sin scroll propio y alineación perfecta */
        overflow: hidden;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        /* ELIMINAR cualquier espacio entre filas */
        line-height: 0;
        font-size: 0;
        /* Asegurar que cada fila ocupe exactamente su altura */
        flex-shrink: 0;
    }
    .gantt-grid-row::-webkit-scrollbar {
        display: none !important;
    }
    
    /* Asegurar que ningún elemento interno tenga scroll no deseado */
    .gantt-grid *,
    .gantt-timeline *,
    .gantt-grid-row *,
    .gantt-grid-cell * {
        overflow: hidden !important;
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }
    
    /* === RESET COMPLETO PARA ALINEACIÓN PERFECTA === */
    .gantt-sidebar-content,
    .gantt-sidebar-content *,
    .gantt-grid,
    .gantt-grid * {
        margin: 0 !important;
        padding: 0;
        border: 0;
        vertical-align: baseline;
    }

    /* === ALINEACIÓN PERFECTA: TODOS 60px === */
    .gantt-grid-centro-header,
    .gantt-grid-maquinaria-row,
    .gantt-grid-row[data-type="centro"],
    .gantt-grid-row[data-type="maquinaria"] {
        height: 60px !important;
        min-height: 60px !important;
        max-height: 60px !important;
        box-sizing: border-box !important;
        /* Línea inferior sutil para separar filas - color más visible */
        box-shadow: inset 0 -1px 0 0 #dee2e6 !important;
        border-bottom: 1px solid #e9ecef !important; /* Línea adicional para asegurar visibilidad */
    }

    /* Asegurar que elementos del sidebar también mantengan 60px */
    .gantt-centro-header {
        height: 60px !important;
        min-height: 60px !important;
        max-height: 60px !important;
        box-sizing: border-box !important;
        padding: 10px 20px !important;
    }
    
    .gantt-grid-cell {
        width: 50px;
        min-width: 50px;
        height: 100%; /* Se adapta a la altura de la fila contenedora */
        border: none; /* Sin border que afecte el tamaño */
        background: #ffffff;
        transition: background-color 0.2s ease;
        flex-shrink: 0; /* Evitar que se compriman las celdas */
        overflow: hidden !important;
        box-sizing: border-box; /* Asegurar cálculo consistente */
        /* Línea derecha usando box-shadow para no afectar tamaño */
        box-shadow: inset -1px 0 0 0 #e0e0e0;
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
        z-index: 50; /* Entre grilla (1) y sidebar (300) */
        cursor: move;
        color: white;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        /* Sin transformaciones automáticas - usar posicionamiento directo */
        transform: none;
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
        /* Mantener sin transformaciones adicionales */
        transform: none;
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

    /* Responsive del Header */
    @media (max-width: 768px) {
        .gantt-header {
            flex-direction: column;
            gap: 8px; /* Reducido */
            padding: 8px 15px;
            min-height: 40px;
            max-height: none;
        }
        
        .gantt-header-left,
        .gantt-header-center,
        .gantt-header-right {
            min-width: unset;
            max-width: none;
            flex: unset;
        }
        
        .gantt-header-center {
            order: 3;
        }
        
        .gantt-header-right {
            order: 2;
            justify-content: center;
        }
        
        .gantt-title {
            font-size: 1.4rem; /* Reducido */
            text-align: center;
        }
        
        .gantt-month-navigation {
            height: 28px; /* Más compacto en móvil */
            padding: 8px 14px;
        }
        
        .gantt-nav-btn,
        .gantt-nav-btn-today {
            height: 22px;
            padding: 3px 6px;
            font-size: 0.7rem;
            min-width: 40px;
        }
        
        .gantt-search-input {
            width: 140px; /* Más estrecho en móvil */
            font-size: 0.75rem;
        }
        
        .search-container {
            height: 28px; /* Coherente con navegación */
        }
    }
    
    @media (max-width: 480px) {
        .gantt-header {
            padding: 6px 10px;
        }
        
        .gantt-title {
            font-size: 1.2rem;
        }
        
        .gantt-search-input {
            width: 120px;
            font-size: 0.7rem;
        }
        
        .gantt-month-navigation {
            height: 26px;
            padding: 2px 6px;
        }
        
        .gantt-nav-btn,
        .gantt-nav-btn-today {
            height: 20px;
            padding: 2px 4px;
            font-size: 0.65rem;
            min-width: 35px;
        }
        
        .gantt-nav-btn span {
            display: none; /* Ocultar texto en móviles pequeños */
        }
    }

    /* Media query optimizada para pantallas grandes */
    @media (min-width: 1024px) {
        .gantt-timeline-container {
            overflow-x: auto !important;
            overflow-y: auto !important;
        }
        
        .gantt-timeline {
            overflow: hidden !important; /* Sin scroll interno */
        }
        
        .gantt-timeline-header {
            overflow: hidden !important; /* Sin scroll interno para sincronización */
            will-change: transform;
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
</style>
<!--
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
        
        // CRÍTICO: Re-sincronizar la grilla después del colapso/expansión
        setTimeout(() => {
            syncCollapseWithGrid(); // Esto re-renderiza la grilla completa
            setDynamicHeights(); // Ajustar alturas dinámicas
            
            // Re-configurar scroll después de cambios en la estructura
            if (window.ganttScrollConfigured) {
                console.log('Re-configurando scroll después de toggle...');
                // El scroll ya está configurado, solo necesitamos asegurar que funcione
            }
        }, 350); // Esperar a que termine la animación
    }
    
    // FUNCIÓN ELIMINADA: adjustDynamicHeights() - Ya no es necesaria
    // Usamos setDynamicHeights() que es más precisa
    }
    
    // FUNCIÓN DESACTIVADA: Sistema de scroll ya configurado en el primer script
    function syncHeaderScroll() {
        console.log('syncHeaderScroll DESACTIVADO - usando sistema unificado del primer script');
        // Esta función ya no es necesaria porque el scroll se maneja en syncScroll()
    }
    
    // Función para actualizar las posiciones de las barras de tareas
    // FUNCIÓN SIMPLIFICADA: Solo actualizar posiciones - OBSOLETA
    // Ya no es necesaria porque renderTaskBars() maneja todo automáticamente
    function updateTaskPositions() {
        // Esta función ahora solo re-renderiza las barras
        renderTaskBars();
        console.log('Posiciones de tareas actualizadas via re-renderizado');
    }
    
    // Hacer la función global para poder usarla desde el HTML
    window.toggleCentroGroup = toggleCentroGroup;
    
    // Inicializar scroll simple - solo header horizontal
    syncHeaderScroll();
    
    // Ajustar alturas iniciales
    adjustDynamicHeights();
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
        
        // Función para sincronizar el scroll vertical (sidebar -> grid) - ELIMINADA - no necesaria
        // Función para sincronizar el scroll horizontal (solo header) - ELIMINADA - simplificada
        
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
    if (prevSemesterBtn) {
        prevSemesterBtn.addEventListener('click', function() {
            navigateSemester(-1);
        });
    }
    
    if (nextSemesterBtn) {
        nextSemesterBtn.addEventListener('click', function() {
            navigateSemester(1);
        });
    }
    
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
    
    // Event listeners simplificados
    window.addEventListener('resize', function() {
        setTimeout(adjustDynamicHeights, 100);
    });
    
    // Ajuste inicial simple
    setTimeout(() => {
        adjustDynamicHeights();
        console.log('Sistema de scroll simplificado inicializado');
    }, 500);
});
</script>
-->
@endsection
