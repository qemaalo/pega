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
    console.log('🚀 SCRIPT INICIADO - DOM CARGADO');
    
    const ganttGrid = document.querySelector('.gantt-grid');
    const ganttBody = document.querySelector('.gantt-body');
    const ganttTimelineContainer = document.getElementById('ganttTimelineContainer');
    const ganttTimeline = document.getElementById('ganttTimeline');
    const sidebar = document.getElementById('ganttSidebar');
    
    console.log('📋 Elementos DOM encontrados:', {
        ganttGrid: !!ganttGrid,
        ganttBody: !!ganttBody, 
        ganttTimelineContainer: !!ganttTimelineContainer,
        ganttTimeline: !!ganttTimeline,
        sidebar: !!sidebar
    });
    
    console.log('📊 Verificando datos:', window.ganttData);
    
    const { centros, tasks, startDate, endDate, totalDays } = window.ganttData;

    // FUNCIÓN GLOBAL: Configurar eventos de barras de tareas con drag & drop completo
    window.setupTaskBarEvents = function(taskBar) {
        console.log('🎯 EJECUTANDO setupTaskBarEvents para:', taskBar.getAttribute('data-task-id'));
        
        // Verificar si la tarea está inactiva
        if (taskBar.getAttribute('data-activo') === "0") {
            console.log('⚠️ Tarea inactiva, saltando configuración');
            return;
        }

        // Variables de estado para esta barra específica
        let isDragging = false;
        let isResizing = false;
        let resizeType = null; // 'left' o 'right'
        let initialMouseX = 0;
        let initialMouseY = 0;
        let initialLeft = 0;
        let initialTop = 0;
        let initialWidth = 0;
        let draggedToNewMaquinaria = false;

        // Variables para las posiciones de las maquinarias
        const maquinariaPositions = JSON.parse(ganttGrid.dataset.maquinariaPositions || '{}');
        const rowHeight = 60;
        const dayWidth = 50;

        // FUNCIÓN: Formatear fecha para envío al servidor
        function formatDateForServer(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // FUNCIÓN: Calcular fechas desde posición en pixels
        function calculateDatesFromPosition(leftPx, widthPx) {
            const startDay = Math.max(0, Math.floor(leftPx / dayWidth));
            const durationDays = Math.max(1, Math.floor(widthPx / dayWidth));
            
            // Crear fecha base usando el mismo método que addDays
            const parts = startDate.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1;
            const day = parseInt(parts[2]);
            
            const calculatedStartDate = new Date(year, month, day);
            calculatedStartDate.setDate(calculatedStartDate.getDate() + startDay);

            const calculatedEndDate = new Date(year, month, day);
            calculatedEndDate.setDate(calculatedEndDate.getDate() + startDay + durationDays - 1);

            console.log('Cálculo de fechas:', {
                leftPx,
                widthPx,
                startDay,
                durationDays,
                startDateStr: startDate,
                calculatedStartDate: formatDate(calculatedStartDate),
                calculatedEndDate: formatDate(calculatedEndDate)
            });

            return {
                startDate: calculatedStartDate,
                endDate: calculatedEndDate
            };
        }

        // FUNCIÓN: Obtener maquinaria desde coordenada Y
        function getMaquinariaFromY(y) {
            let closestMaquinariaId = null;
            let closestDistance = Infinity;
            
            Object.keys(maquinariaPositions).forEach(maqId => {
                const maqY = maquinariaPositions[maqId] + (rowHeight / 2);
                const distance = Math.abs(y - maqY);
                if (distance < closestDistance && distance < rowHeight / 2) {
                    closestDistance = distance;
                    closestMaquinariaId = maqId;
                }
            });
            
            return closestMaquinariaId;
        }

        // FUNCIÓN: Enviar actualización al servidor usando tu método ajaxUpdate
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
            
            // Usar la misma lógica de URL de tu código de ejemplo
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
                // Revertir posición en caso de error
                renderTaskBars();
            });
        }

        // FUNCIÓN: Mostrar notificación (adaptada de tu código)
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
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // EVENT LISTENERS GLOBALES para mousemove y mouseup
        function handleMouseMove(e) {
            if (!isDragging && !isResizing) return;

            const timelineRect = ganttTimeline.getBoundingClientRect();
            const mouseX = e.clientX - timelineRect.left;
            const mouseY = e.clientY - timelineRect.top;

            if (isDragging) {
                // DRAG: Mover toda la barra
                const deltaX = e.clientX - initialMouseX;
                const deltaY = e.clientY - initialMouseY;
                
                let newLeft = initialLeft + deltaX;
                let newTop = initialTop + deltaY;
                
                // Snap a la grilla de días (ajuste horizontal)
                newLeft = Math.round(newLeft / dayWidth) * dayWidth;
                
                // Limitar a los bordes del timeline
                newLeft = Math.max(0, Math.min(newLeft, (totalDays - 1) * dayWidth));
                
                // Snap a filas de maquinarias (ajuste vertical)
                let targetMaquinariaId = getMaquinariaFromY(mouseY);
                if (targetMaquinariaId && maquinariaPositions[targetMaquinariaId]) {
                    newTop = maquinariaPositions[targetMaquinariaId] + (rowHeight - 45) / 2;
                    
                    // Marcar si cambió de maquinaria
                    const originalMaquinariaId = taskBar.getAttribute('data-maquinaria-id');
                    if (targetMaquinariaId !== originalMaquinariaId) {
                        draggedToNewMaquinaria = true;
                        taskBar.setAttribute('data-maquinaria-id', targetMaquinariaId);
                    }
                }
                
                taskBar.style.left = newLeft + 'px';
                taskBar.style.top = newTop + 'px';
                
            } else if (isResizing) {
                // RESIZE: Cambiar ancho de la barra
                const deltaX = e.clientX - initialMouseX;
                
                if (resizeType === 'left') {
                    // Resize desde la izquierda (cambiar inicio)
                    let newLeft = initialLeft + deltaX;
                    newLeft = Math.round(newLeft / dayWidth) * dayWidth;
                    newLeft = Math.max(0, Math.min(newLeft, initialLeft + initialWidth - dayWidth));
                    
                    let newWidth = initialLeft + initialWidth - newLeft;
                    newWidth = Math.max(dayWidth, newWidth);
                    
                    taskBar.style.left = newLeft + 'px';
                    taskBar.style.width = newWidth + 'px';
                    
                    console.log('Resize izquierdo:', { newLeft, newWidth });
                    
                } else if (resizeType === 'right') {
                    // Resize desde la derecha (cambiar duración)
                    let newWidth = initialWidth + deltaX;
                    newWidth = Math.round(newWidth / dayWidth) * dayWidth;
                    newWidth = Math.max(dayWidth, Math.min(newWidth, (totalDays * dayWidth) - initialLeft));
                    
                    taskBar.style.width = newWidth + 'px';
                    
                    console.log('Resize derecho:', { newWidth });
                }
            }
            
            e.preventDefault();
        }

        function handleMouseUp(e) {
            if (!isDragging && !isResizing) return;

            if (isDragging) {
                isDragging = false;
                taskBar.classList.remove('dragging');
                
                // Calcular nuevas fechas y actualizar atributos
                const newLeft = parseInt(taskBar.style.left);
                const width = parseInt(taskBar.style.width);
                
                const dates = calculateDatesFromPosition(newLeft, width);
                
                taskBar.setAttribute('data-start-date', formatDateForServer(dates.startDate));
                taskBar.setAttribute('data-end-date', formatDateForServer(dates.endDate));
                
                console.log('Drag finalizado:', {
                    newLeft,
                    width,
                    startDate: formatDateForServer(dates.startDate),
                    endDate: formatDateForServer(dates.endDate),
                    maquinariaId: taskBar.getAttribute('data-maquinaria-id')
                });
                
                // Enviar actualización al servidor
                updateTaskDatesInDB(taskBar);
                
            } else if (isResizing) {
                isResizing = false;
                resizeType = null;
                taskBar.classList.remove('resizing');
                
                // Calcular nuevas fechas y actualizar atributos
                const newLeft = parseInt(taskBar.style.left);
                const width = parseInt(taskBar.style.width);
                
                const dates = calculateDatesFromPosition(newLeft, width);
                
                taskBar.setAttribute('data-start-date', formatDateForServer(dates.startDate));
                taskBar.setAttribute('data-end-date', formatDateForServer(dates.endDate));
                
                console.log('Resize finalizado:', {
                    newLeft,
                    width,
                    startDate: formatDateForServer(dates.startDate),
                    endDate: formatDateForServer(dates.endDate)
                });
                
                // Enviar actualización al servidor
                updateTaskDatesInDB(taskBar);
            }
            
            // Limpiar estado
            draggedToNewMaquinaria = false;
            
            // Remover listeners globales
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);
        }

        // EVENT LISTENER: Drag principal de la barra
        taskBar.addEventListener('mousedown', function(e) {
            if (taskBar.getAttribute('data-activo') === "0") return;
            
            // No iniciar drag si se hace clic en un resizer
            if (e.target.classList.contains('gantt-task-resizer-left') || 
                e.target.classList.contains('gantt-task-resizer-right')) {
                return;
            }
            
            console.log('🎯 INICIANDO DRAG de barra:', taskBar.getAttribute('data-task-id'));
            
            isDragging = true;
            taskBar.classList.add('dragging');
            
            initialMouseX = e.clientX;
            initialMouseY = e.clientY;
            initialLeft = parseInt(taskBar.style.left) || 0;
            initialTop = parseInt(taskBar.style.top) || 0;
            initialWidth = parseInt(taskBar.style.width) || 100;
            
            // Agregar listeners globales
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
            
            e.preventDefault();
            e.stopPropagation();
        });

        // EVENT LISTENER: Resize izquierdo
        const resizerLeft = taskBar.querySelector('.gantt-task-resizer-left');
        if (resizerLeft) {
            // Test básico: agregar evento de mouseenter para verificar que el elemento recibe eventos
            resizerLeft.addEventListener('mouseenter', function(e) {
                console.log('🟢 Mouse entró en resizer izquierdo');
                resizerLeft.style.backgroundColor = 'rgba(0, 255, 0, 0.9)'; // Verde cuando funciona
            });
            
            resizerLeft.addEventListener('mouseleave', function(e) {
                console.log('🔴 Mouse salió de resizer izquierdo');
                resizerLeft.style.backgroundColor = 'rgba(255, 0, 0, 0.8)'; // Volver a rojo
            });
            
            resizerLeft.addEventListener('mousedown', function(e) {
                if (taskBar.getAttribute('data-activo') === "0") return;
                
                console.log('🎯 INICIANDO RESIZE IZQUIERDO de barra:', taskBar.getAttribute('data-task-id'));
                
                isResizing = true;
                resizeType = 'left';
                taskBar.classList.add('resizing');
                
                initialMouseX = e.clientX;
                initialLeft = parseInt(taskBar.style.left) || 0;
                initialWidth = parseInt(taskBar.style.width) || 100;
                
                // Agregar listeners globales
                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);
                
                console.log('Resize izquierdo iniciado:', { initialLeft, initialWidth });
                e.stopPropagation();
                e.preventDefault();
            });
        }

        // EVENT LISTENER: Resize derecho
        const resizerRight = taskBar.querySelector('.gantt-task-resizer-right');
        if (resizerRight) {
            // Test básico: agregar evento de mouseenter para verificar que el elemento recibe eventos
            resizerRight.addEventListener('mouseenter', function(e) {
                console.log('🟢 Mouse entró en resizer derecho');
                resizerRight.style.backgroundColor = 'rgba(0, 255, 0, 0.9)'; // Verde cuando funciona
            });
            
            resizerRight.addEventListener('mouseleave', function(e) {
                console.log('🔴 Mouse salió de resizer derecho');
                resizerRight.style.backgroundColor = 'rgba(255, 0, 0, 0.8)'; // Volver a rojo
            });
            
            resizerRight.addEventListener('mousedown', function(e) {
                if (taskBar.getAttribute('data-activo') === "0") return;
                
                console.log('🎯 INICIANDO RESIZE DERECHO de barra:', taskBar.getAttribute('data-task-id'));
                
                isResizing = true;
                resizeType = 'right';
                taskBar.classList.add('resizing');
                
                initialMouseX = e.clientX;
                initialLeft = parseInt(taskBar.style.left) || 0;
                initialWidth = parseInt(taskBar.style.width) || 100;
                
                // Agregar listeners globales
                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);
                
                console.log('Resize derecho iniciado:', { initialLeft, initialWidth });
                e.stopPropagation();
                e.preventDefault();
            });
        }

        // Debug: verificar que los redimensionadores existen
        console.log('🔍 Configurando barra:', taskBar.getAttribute('data-task-id'), {
            hasLeftResizer: !!resizerLeft,
            hasRightResizer: !!resizerRight,
            leftResizerElement: resizerLeft,
            rightResizerElement: resizerRight,
            taskBarChildren: Array.from(taskBar.children).map(child => child.className)
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

    // Utilidades de fecha - CORREGIDAS para evitar problemas de zona horaria
    function addDays(dateStr, days) {
        // Crear fecha usando el constructor año, mes-1, día para evitar problemas de zona horaria
        const parts = dateStr.split('-');
        const year = parseInt(parts[0]);
        const month = parseInt(parts[1]) - 1; // Los meses en JavaScript van de 0-11
        const day = parseInt(parts[2]);
        
        const d = new Date(year, month, day);
        d.setDate(d.getDate() + days);
        return d;
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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
                cell.dataset.date = formatDate(currentDate); // Agregar fecha para click detection
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
                        cell.dataset.date = formatDate(currentDate); // Fecha para click detection
                        cell.dataset.maquinariaId = maquinaria.id; // ID de maquinaria para pre-selección
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
        const ganttTimelineElement = document.getElementById('ganttTimeline');
        if (!ganttTimelineElement) {
            console.error('Error: No se encontró ganttTimeline');
            return;
        }
        
        // Eliminar barras previas
        const prevBars = ganttTimelineElement.querySelectorAll('.gantt-task-bar');
        prevBars.forEach(bar => bar.remove());
        
        const maquinariaPositions = JSON.parse(ganttGrid.dataset.maquinariaPositions || '{}');
        
        if (Object.keys(maquinariaPositions).length === 0) {
            console.error('Error: No hay posiciones de maquinarias disponibles');
            return;
        }
        
        let tareasCreadas = 0;
        
        window.ganttData.tasks.forEach((task, index) => {
            if (!task.finicio || !task.ftermino) {
                return; // Saltamos tareas sin fechas
            }
            
            // Usar el mismo método de creación de fechas para evitar problemas de zona horaria
            const taskStartParts = task.finicio.split('-');
            const taskStartDate = new Date(parseInt(taskStartParts[0]), parseInt(taskStartParts[1]) - 1, parseInt(taskStartParts[2]));
            
            const taskEndParts = task.ftermino.split('-');
            const taskEndDate = new Date(parseInt(taskEndParts[0]), parseInt(taskEndParts[1]) - 1, parseInt(taskEndParts[2]));
            
            // Calcular posición y dimensiones usando el mismo método
            const startParts = startDate.split('-');
            const startDateObj = new Date(parseInt(startParts[0]), parseInt(startParts[1]) - 1, parseInt(startParts[2]));
            
            const endParts = endDate.split('-');
            const endDateObj = new Date(parseInt(endParts[0]), parseInt(endParts[1]) - 1, parseInt(endParts[2]));
            
            let effectiveStart = taskStartDate < startDateObj ? startDateObj : taskStartDate;
            let effectiveEnd = taskEndDate > endDateObj ? endDateObj : taskEndDate;
            
            const taskStartDay = Math.floor((effectiveStart - startDateObj) / (1000*60*60*24));
            const taskEndDay = Math.floor((effectiveEnd - startDateObj) / (1000*60*60*24));
            
            let leftPosition = taskStartDay * 50;
            let width = Math.max(25, (taskEndDay - taskStartDay + 1) * 50);
            
            // Calcular posición vertical
            const maquinariaId = task.maquinaria_id;
            if (!maquinariaPositions[maquinariaId]) {
                return; // Saltamos tareas sin maquinaria válida
            }
            
            const rowHeight = 60;
            const barHeight = 45;
            const topPosition = maquinariaPositions[maquinariaId] + (rowHeight - barHeight) / 2;
            
            // Determinar color por centro
            let barColor = '#4a6cf7'; // Color por defecto
            if (task.maquinaria && task.maquinaria.centro) {
                const centerColors = {
                    'PRENSA': '#ff6b6b',
                    'REVESTIMIENTO': '#4ecdc4',
                    'POLIURETANO': '#45b7d1',
                    'TRAFILA': '#96ceb4',
                    'ANILLOS': '#feca57'
                };
                barColor = centerColors[task.maquinaria.centro.descripcion] || barColor;
            }
            
            // Crear barra de tarea
            const bar = createTaskBar(task, leftPosition, width, topPosition, barHeight, barColor);
            ganttTimelineElement.appendChild(bar);
            
            tareasCreadas++;
        });
        
        console.log(`Creadas ${tareasCreadas} barras de tareas con funcionalidad de resize`);
    }
    
    // Función auxiliar para crear una barra de tarea completa
    function createTaskBar(task, leftPosition, width, topPosition, height, backgroundColor) {
        const bar = document.createElement('div');
        bar.className = 'gantt-task-bar';
        
        // Crear fechas usando el método consistente
        const startParts = task.finicio.split('-');
        const taskStartDate = new Date(parseInt(startParts[0]), parseInt(startParts[1]) - 1, parseInt(startParts[2]));
        
        const endParts = task.ftermino.split('-');
        const taskEndDate = new Date(parseInt(endParts[0]), parseInt(endParts[1]) - 1, parseInt(endParts[2]));
        
        // Atributos de datos
        bar.setAttribute('data-task-id', task.id);
        bar.setAttribute('data-start-date', formatDate(taskStartDate));
        bar.setAttribute('data-end-date', formatDate(taskEndDate));
        bar.setAttribute('data-maquinaria-id', task.maquinaria_id || '');
        bar.setAttribute('data-activo', task.activo);
        bar.setAttribute('data-centro', task.maquinaria?.centro?.descripcion || '');
        
        // Estilos
        bar.style.left = leftPosition + 'px';
        bar.style.width = width + 'px';
        bar.style.top = topPosition + 'px';
        bar.style.height = height + 'px';
        bar.style.backgroundColor = backgroundColor;
        bar.style.position = 'absolute';
        
        // Tooltip usando las fechas ya calculadas correctamente
        const maquinariaNombre = (task.maquinaria && task.maquinaria.nombre) ? task.maquinaria.nombre : 'Sin maquinaria';
        const fechaInicio = formatDate(taskStartDate).slice(5);
        const fechaTermino = formatDate(taskEndDate).slice(5);
        bar.title = `OP ${task.op}-${task.linea} | ${maquinariaNombre} | ${fechaInicio} - ${fechaTermino}`;
        
        // Contenido
        const content = document.createElement('div');
        content.className = 'gantt-task-content';
        const label = document.createElement('span');
        label.className = 'gantt-task-label';
        label.textContent = `OP ${task.op}-${task.linea}`;
        content.appendChild(label);
        bar.appendChild(content);
        
        // Crear resizers
        createResizers(bar);
        
        return bar;
    }
    
    // Función auxiliar para crear los elementos resizer
    function createResizers(bar) {
        // Resizer izquierdo
        const resizerLeft = document.createElement('div');
        resizerLeft.className = 'gantt-task-resizer-left';
        addResizeEvents(resizerLeft, bar, 'left');
        bar.appendChild(resizerLeft);
        
        // Resizer derecho
        const resizerRight = document.createElement('div');
        resizerRight.className = 'gantt-task-resizer-right';
        addResizeEvents(resizerRight, bar, 'right');
        bar.appendChild(resizerRight);
    }
    
    // Función auxiliar para manejar eventos de resize
    function addResizeEvents(resizer, bar, side) {
        resizer.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const startMouseX = e.clientX;
            const startLeft = parseInt(bar.style.left) || 0;
            const startWidth = parseInt(bar.style.width) || 100;
            
            const minWidth = 50; // Ancho mínimo de la barra
            
            function onMouseMove(moveEvent) {
                const deltaX = moveEvent.clientX - startMouseX;
                
                if (side === 'left') {
                    // Resize desde la izquierda
                    const newLeft = startLeft + deltaX;
                    const newWidth = startLeft + startWidth - newLeft;
                    
                    if (newWidth >= minWidth) {
                        bar.style.left = newLeft + 'px';
                        bar.style.width = newWidth + 'px';
                    }
                } else {
                    // Resize desde la derecha
                    const newWidth = startWidth + deltaX;
                    
                    if (newWidth >= minWidth) {
                        bar.style.width = newWidth + 'px';
                    }
                }
            }
            
            function onMouseUp() {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
                
                // Aquí se podría llamar a updateTaskInDatabase si es necesario
                console.log(`Tarea ${bar.getAttribute('data-task-id')} redimensionada`);
            }
            
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    }
        
    // Configurar eventos de drag & drop para todas las barras después de renderizar
    setTimeout(() => {
        const allBars = document.querySelectorAll('.gantt-task-bar');
        allBars.forEach((bar) => {
            if (window.setupTaskBarEvents) {
                window.setupTaskBarEvents(bar);
            }
        });
    }, 100);

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
    console.log('🎯 Datos disponibles:', window.ganttData);
    console.log('🎯 Total tareas:', window.ganttData.tasks.length);
    
    try {
        console.log('📊 Llamando renderGrid()...');
        renderGrid();
        console.log('✅ renderGrid() completado');
        
        console.log('📊 Llamando renderTaskBars()...');
        renderTaskBars();
        console.log('✅ renderTaskBars() completado');
        
        // TEST: Verificar si existen resizers después del renderizado
        setTimeout(() => {
            const allResizers = document.querySelectorAll('.gantt-task-resizer-left, .gantt-task-resizer-right');
            console.log('🔍 RESIZERS ENCONTRADOS:', allResizers.length);
            allResizers.forEach((resizer, index) => {
                console.log(`Resizer ${index + 1}:`, resizer.className, resizer.style.cssText);
            });
            
            if (allResizers.length === 0) {
                console.error('❌ NO SE ENCONTRARON RESIZERS - PROBLEMA EN LA CREACIÓN');
            } else {
                console.log('✅ Resizers encontrados correctamente');
            }
        }, 100);
        
        console.log('📊 Llamando setDynamicHeights()...');
        setDynamicHeights();
        console.log('✅ setDynamicHeights() completado');
        
    } catch (error) {
        console.error('❌ ERROR EN INICIALIZACIÓN:', error);
        console.error('❌ Stack trace:', error.stack);
    }
    
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
    
    // CONFIGURAR NAVEGACIÓN DE SEMESTRES
    console.log('🧭 Configurando navegación de semestres...');
    
    const prevSemesterBtn = document.getElementById('prevSemester');
    const nextSemesterBtn = document.getElementById('nextSemester');
    const currentSemesterBtn = document.getElementById('currentSemesterBtn');
    
    console.log('🔍 Elementos de navegación encontrados:', {
        prevSemesterBtn: !!prevSemesterBtn,
        nextSemesterBtn: !!nextSemesterBtn,
        currentSemesterBtn: !!currentSemesterBtn
    });
    
    if (prevSemesterBtn) {
        prevSemesterBtn.addEventListener('click', function() {
            console.log('🔙 Navegando al semestre anterior');
            navigateToSemester('prev');
        });
        console.log('✅ Event listener agregado a prevSemester');
    } else {
        console.error('❌ Botón prevSemester no encontrado');
    }
    
    if (nextSemesterBtn) {
        nextSemesterBtn.addEventListener('click', function() {
            console.log('🔜 Navegando al semestre siguiente');
            navigateToSemester('next');
        });
        console.log('✅ Event listener agregado a nextSemester');
    } else {
        console.error('❌ Botón nextSemester no encontrado');
    }
    
    if (currentSemesterBtn) {
        currentSemesterBtn.addEventListener('click', function() {
            console.log('📅 Navegando al semestre actual');
            navigateToCurrentSemester();
        });
        console.log('✅ Event listener agregado a currentSemester');
    } else {
        console.error('❌ Botón currentSemester no encontrado');
    }
    
    // FUNCIÓN: Navegar a semestre anterior/siguiente
    function navigateToSemester(direction) {
        const currentUrl = new URL(window.location);
        const params = new URLSearchParams(currentUrl.search);
        
        // Obtener semestre y año actuales desde los parámetros o datos del controlador
        let currentSemester = parseInt(params.get('semester')) || {{ $currentSemester }};
        let currentYear = parseInt(params.get('year')) || {{ $currentYear }};
        
        console.log('📅 Navegación actual:', {
            currentSemester,
            currentYear,
            direction
        });
        
        // Calcular nuevo semestre y año
        if (direction === 'prev') {
            if (currentSemester === 1) {
                currentSemester = 2;
                currentYear--;
            } else {
                currentSemester = 1;
            }
        } else if (direction === 'next') {
            if (currentSemester === 2) {
                currentSemester = 1;
                currentYear++;
            } else {
                currentSemester = 2;
            }
        }
        
        console.log('📅 Nuevo destino:', {
            semester: currentSemester,
            year: currentYear
        });
        
        // Actualizar parámetros URL usando el sistema del controller
        params.set('semester', currentSemester);
        params.set('year', currentYear);
        
        // Mantener búsqueda si existe
        if (params.get('search_op')) {
            // Mantener el search_op pero actualizar el semestre
            console.log('🔍 Manteniendo búsqueda:', params.get('search_op'));
        }
        
        // Navegar a la nueva URL
        const newUrl = currentUrl.pathname + '?' + params.toString();
        console.log('🚀 Navegando a:', newUrl);
        window.location.href = newUrl;
    }
    
    // FUNCIÓN: Navegar al semestre actual
    function navigateToCurrentSemester() {
        const currentUrl = new URL(window.location);
        const params = new URLSearchParams(currentUrl.search);
        
        // Calcular semestre actual basado en la fecha de hoy
        const today = new Date();
        const currentMonth = today.getMonth() + 1; // getMonth() returns 0-11
        const currentSemester = currentMonth <= 6 ? 1 : 2;
        const currentYear = today.getFullYear();
        
        console.log('📅 Navegando al semestre actual:', {
            currentMonth,
            semester: currentSemester,
            year: currentYear
        });
        
        // Actualizar parámetros URL
        params.set('semester', currentSemester);
        params.set('year', currentYear);
        
        // Mantener búsqueda si existe
        if (params.get('search_op')) {
            console.log('🔍 Manteniendo búsqueda:', params.get('search_op'));
        }
        
        // Navegar a la nueva URL
        const newUrl = currentUrl.pathname + '?' + params.toString();
        console.log('🚀 Navegando a semestre actual:', newUrl);
        window.location.href = newUrl;
    }
    
    // Configurar clicks en grid después de que se renderice
    setTimeout(() => {
        setupGridCellClicks();
    }, 1000);
});

// =============================
// FUNCIONES GLOBALES DEL MODAL
// =============================

function openNewTaskModal(clickedDate, maquinariaId = null) {
    console.log('Abriendo modal para fecha:', clickedDate, 'Maquinaria ID:', maquinariaId);
    
    const modal = document.getElementById('newTaskModal');
    const inicioInput = document.getElementById('taskFinicio');
    const terminoInput = document.getElementById('taskFtermino');
    const maquinariaSelect = document.getElementById('taskMaquinaria');
    const selectedMachineInfo = document.getElementById('selectedMachineInfo');
    const selectedMachineName = document.getElementById('selectedMachineName');
    
    // Pre-llenar fecha de inicio con la fecha clickeada
    inicioInput.value = clickedDate;
    
    // Calcular fecha de término (una semana después por defecto)
    const startDate = new Date(clickedDate);
    const endDate = new Date(startDate);
    endDate.setDate(startDate.getDate() + 7);
    terminoInput.value = endDate.toISOString().split('T')[0];
    
    // Pre-seleccionar maquinaria si se proporcionó
    if (maquinariaId && maquinariaSelect) {
        maquinariaSelect.value = maquinariaId;
        
        // Mostrar información de la maquinaria seleccionada
        const selectedOption = maquinariaSelect.options[maquinariaSelect.selectedIndex];
        if (selectedOption && selectedOption.text !== 'Seleccionar maquinaria') {
            selectedMachineName.textContent = selectedOption.text;
            selectedMachineInfo.style.display = 'block';
        }
        
        console.log('Maquinaria pre-seleccionada:', maquinariaId);
    } else {
        // Ocultar información si no hay maquinaria seleccionada
        selectedMachineInfo.style.display = 'none';
    }
    
    // Mostrar modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Enfocar el primer campo
    setTimeout(() => {
        document.getElementById('taskOp').focus();
    }, 300);
}

function closeNewTaskModal() {
    const modal = document.getElementById('newTaskModal');
    const selectedMachineInfo = document.getElementById('selectedMachineInfo');
    
    modal.style.display = 'none';
    document.body.style.overflow = '';
    
    // Limpiar formulario
    document.getElementById('newTaskForm').reset();
    
    // Ocultar información de maquinaria seleccionada
    if (selectedMachineInfo) {
        selectedMachineInfo.style.display = 'none';
    }
}

function submitNewTask(event) {
    event.preventDefault();
    
    const form = document.getElementById('newTaskForm');
    const formData = new FormData(form);
    
    // Mostrar loading en el botón
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creando...';
    submitBtn.disabled = true;
    
    fetch('/compromops', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            closeNewTaskModal();
            
            // Mostrar mensaje de éxito
            showNotification('Tarea creada exitosamente', 'success');
            
            // Recargar la página para mostrar la nueva tarea
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Error al crear la tarea');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al crear la tarea: ' + error.message, 'error');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
    
    return false;
}

function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Estilos inline para la notificación
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '4px',
        color: 'white',
        fontSize: '14px',
        zIndex: '10001',
        maxWidth: '400px',
        opacity: '0',
        transform: 'translateX(100%)',
        transition: 'all 0.3s ease'
    });
    
    // Colores según tipo
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#28a745';
            break;
        case 'error':
            notification.style.backgroundColor = '#dc3545';
            break;
        default:
            notification.style.backgroundColor = '#007bff';
    }
    
    document.body.appendChild(notification);
    
    // Animación de entrada
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 4 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Función para detectar clicks en celdas del grid
function setupGridCellClicks() {
    console.log('Configurando clicks en celdas del grid...');
    
    const ganttBody = document.querySelector('.gantt-body');
    if (!ganttBody) {
        console.error('No se encontró .gantt-body');
        return;
    }
    
    ganttBody.addEventListener('click', function(e) {
        console.log('Click detectado en:', e.target);
        
        // Verificar si el click fue en una celda vacía (no en una tarea)
        if (e.target.classList.contains('gantt-grid-cell') || 
            e.target.closest('.gantt-grid-cell')) {
            
            // Verificar que no sea un click en una tarea existente
            if (e.target.closest('.task-bar')) {
                console.log('Click en tarea existente, ignorando');
                return;
            }
            
            const cell = e.target.classList.contains('gantt-grid-cell') ? 
                       e.target : e.target.closest('.gantt-grid-cell');
            
            // Obtener la fecha de la celda
            const dateStr = cell.dataset.date;
            // Obtener el ID de maquinaria de la celda
            const maquinariaId = cell.dataset.maquinariaId;
            
            console.log('Fecha de celda:', dateStr, 'Maquinaria ID:', maquinariaId);
            
            if (dateStr) {
                openNewTaskModal(dateStr, maquinariaId);
            } else {
                console.warn('No se encontró fecha en la celda');
            }
        }
    });
    
    console.log('✅ Clicks en celdas configurados');
}

// Configurar eventos del modal cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal al hacer click fuera
    if (document.getElementById('newTaskModal')) {
        document.getElementById('newTaskModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNewTaskModal();
            }
        });
    }
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeNewTaskModal();
        }
    });
    
    // Event listener para el select de maquinaria
    const maquinariaSelect = document.getElementById('taskMaquinaria');
    if (maquinariaSelect) {
        maquinariaSelect.addEventListener('change', function() {
            const selectedMachineInfo = document.getElementById('selectedMachineInfo');
            const selectedMachineName = document.getElementById('selectedMachineName');
            
            if (this.value && this.selectedIndex > 0) {
                selectedMachineName.textContent = this.options[this.selectedIndex].text;
                selectedMachineInfo.style.display = 'block';
            } else {
                selectedMachineInfo.style.display = 'none';
            }
        });
    }
});
</script>
                    
<style>
    /* Controlar scrolls de manera específica */
    html {
        overflow: hidden;
        height: 100vh;
    }
    
    body {
        overflow: visible; /* Permitir overflow vertical para ver el header completo */
        height: 100vh;
        max-height: 100vh;
    }
    
    /* Asegurar que el container principal sea visible */
    .gantt-container {
        height: 100vh;
        max-height: 100vh;
        overflow: visible;
        display: flex;
        flex-direction: column;
    }
    
    /* Header debe ser siempre visible */
    .gantt-header {
        flex: 0 0 auto !important;
        z-index: 1000 !important;
        position: relative !important;
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Asegurar que los botones de navegación sean visibles y funcionales */
    .gantt-nav-btn, 
    .gantt-nav-btn-today {
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        z-index: 1001 !important;
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
        background-color: #f0f8ff !important;
        cursor: pointer;
        border: 1px solid #007bff;
        transform: scale(1.02);
        transition: all 0.2s ease;
        z-index: 10;
        position: relative;
    }
    
    /* Hover especial para celdas con maquinaria */
    .gantt-grid-cell[data-maquinaria-id]:hover::after {
        content: "Crear tarea aquí";
        position: absolute;
        bottom: -25px;
        left: 50%;
        transform: translateX(-50%);
        background: #007bff;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        white-space: nowrap;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
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
        /* ELIMINADO: border-color que causaba problemas */
        /* Mantener sin transformaciones adicionales */
        transform: none;
    }
    
    .gantt-task-bar.dragging {
        opacity: 0.8;
        transform: scale(1.05);
        z-index: 1000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.35);
        cursor: grabbing !important;
        transition: none !important; /* Deshabilitar transiciones durante el drag */
    }
    
    .gantt-task-bar.resizing {
        opacity: 0.9;
        z-index: 999;
        box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        cursor: col-resize !important;
        transition: none !important;
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
    
    /* Mejoras adicionales para drag & drop */
    .gantt-grid-cell.drag-target {
        background-color: rgba(74, 108, 247, 0.1) !important;
        border: 1px dashed rgba(74, 108, 247, 0.4) !important;
        animation: dragTargetPulse 1s infinite;
    }
    
    @keyframes dragTargetPulse {
        0% { background-color: rgba(74, 108, 247, 0.1); }
        50% { background-color: rgba(74, 108, 247, 0.2); }
        100% { background-color: rgba(74, 108, 247, 0.1); }
    }
    
    /* Indicador de posición durante el drag */
    .drag-position-indicator {
        position: fixed;
        background: rgba(74, 108, 247, 0.9);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        z-index: 1001;
        pointer-events: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        transition: none;
    }
    
    /* Mejorar la visibilidad del cursor durante drag y resize - Estas reglas se consolidaron arriba */
    
    /* TAMBIÉN COMENTADO - Estados de hover mejorados para los resizers
    .gantt-task-resizer-left:hover {
        background-color: rgba(255, 255, 255, 0.9) !important;
        box-shadow: -2px 0 4px rgba(0,0,0,0.2);
    }
    
    .gantt-task-resizer-right:hover {
        background-color: rgba(255, 255, 255, 0.9) !important;
        box-shadow: 2px 0 4px rgba(0,0,0,0.2);
    }
    */
    
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
    
    /* Resizers para redimensionar tareas */
    .gantt-task-resizer-left,
    .gantt-task-resizer-right {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 8px;
        cursor: col-resize;
        opacity: 0;
        transition: opacity 0.2s ease;
        background-color: rgba(255, 255, 255, 0.8);
        z-index: 60;
    }
    
    .gantt-task-resizer-left {
        left: 0;
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
    
    .gantt-task-resizer-right {
        right: 0;
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    
    /* Mostrar resizers al hacer hover sobre la tarea */
    .gantt-task-bar:hover .gantt-task-resizer-left,
    .gantt-task-bar:hover .gantt-task-resizer-right {
        opacity: 0.7;
    }
    
    /* Resaltar al hacer hover específico sobre el resizer */
    .gantt-task-resizer-left:hover,
    .gantt-task-resizer-right:hover {
        opacity: 1 !important;
        background-color: rgba(74, 108, 247, 0.9) !important;
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
    
    /* Estilos del Modal */
    .gantt-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
    }
    
    .gantt-modal-content {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        animation: modalSlideIn 0.3s ease-out;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translate(-50%, -60%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }
    
    .gantt-modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }
    
    .gantt-modal-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.3em;
    }
    
    .selected-machine-info {
        margin-top: 5px;
        padding: 5px 10px;
        background-color: #e3f2fd;
        border-radius: 4px;
        border-left: 3px solid #2196f3;
    }
    
    .selected-machine-info .text-muted {
        color: #666 !important;
        font-size: 0.9em;
    }
    
    .selected-machine-info i {
        color: #2196f3;
        margin-right: 5px;
    }
    
    .gantt-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }
    
    .gantt-modal-close:hover {
        background-color: #e9ecef;
        color: #333;
    }
    
    .gantt-modal-body {
        padding: 20px;
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
    }
    
    .gantt-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background-color: #f8f9fa;
        border-radius: 0 0 8px 8px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    
    .btn-primary {
        background-color: #007bff;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .gantt-modal-content {
            width: 95%;
            margin: 20px;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .gantt-modal-footer {
            flex-direction: column-reverse;
        }
        
        .gantt-modal-footer .btn {
            width: 100%;
        }
    }
</style>

<!-- Modal para crear nueva tarea -->
<div id="newTaskModal" class="gantt-modal">
    <div class="gantt-modal-content">
        <div class="gantt-modal-header">
            <div>
                <h3>Crear Nueva Tarea</h3>
                <div id="selectedMachineInfo" class="selected-machine-info" style="display: none;">
                    <small class="text-muted">
                        <i class="fas fa-cog"></i> Maquinaria seleccionada: <span id="selectedMachineName"></span>
                    </small>
                </div>
            </div>
            <button type="button" class="gantt-modal-close" onclick="closeNewTaskModal()">&times;</button>
        </div>
        
        <form id="newTaskForm" onsubmit="return submitNewTask(event)">
            @csrf
            <div class="gantt-modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="taskOp">OP *</label>
                        <input type="text" id="taskOp" name="op" required placeholder="Número de OP">
                    </div>
                    <div class="form-group">
                        <label for="taskNp">NP *</label>
                        <input type="text" id="taskNp" name="np" required placeholder="Número de NP">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="taskLinea">Línea *</label>
                        <input type="text" id="taskLinea" name="linea" required placeholder="Número de línea">
                    </div>
                    <div class="form-group">
                        <label for="taskUsuario">Usuario *</label>
                        <input type="text" id="taskUsuario" name="usuario" required placeholder="Usuario responsable">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="taskMaquinaria">Maquinaria *</label>
                        <select id="taskMaquinaria" name="maquinaria_id" required>
                            <option value="">Seleccionar maquinaria</option>
                            @foreach($centros as $centro)
                                <optgroup label="{{ $centro->descripcion }}">
                                    @foreach($centro->maquinarias as $maquinaria)
                                        <option value="{{ $maquinaria->id }}">{{ $maquinaria->nombre }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="taskFinicio">Fecha Inicio *</label>
                        <input type="date" id="taskFinicio" name="finicio" required>
                    </div>
                    <div class="form-group">
                        <label for="taskFtermino">Fecha Término *</label>
                        <input type="date" id="taskFtermino" name="ftermino" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="taskObservaciones">Observaciones</label>
                    <textarea id="taskObservaciones" name="observaciones" rows="3" placeholder="Observaciones adicionales"></textarea>
                </div>
            </div>
            
            <div class="gantt-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeNewTaskModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Tarea</button>
            </div>
        </form>
    </div>
</div>

@endsection
