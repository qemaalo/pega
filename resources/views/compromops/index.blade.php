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
        
        <div class="gantt-month-navigation">
            <button id="prevMonth" class="gantt-nav-btn">« Mes anterior</button>
            <div class="gantt-month-container">
                <button id="currentMonthBtn" class="gantt-nav-btn gantt-nav-btn-today">Mes actual</button>
                <span id="currentMonthDisplay" class="gantt-current-month">{{ $dateString }}</span>
            </div>
            <button id="nextMonth" class="gantt-nav-btn">Mes siguiente »</button>
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
                            // Calcular posición horizontal
                            $taskStart = $task->finicio ? date('j', strtotime($task->finicio)) : 1;
                            $taskEnd = $task->ftermino ? date('j', strtotime($task->ftermino)) : $daysInMonth;
                            
                            // Si la fecha no está en el mes actual, ajustar los límites
                            if (date('m', strtotime($task->finicio)) != $currentMonth) $taskStart = 1;
                            if (date('m', strtotime($task->ftermino)) != $currentMonth) $taskEnd = $daysInMonth;
                            
                            $leftPosition = ($taskStart - 1) / $daysInMonth * 100;
                            $width = ($taskEnd - $taskStart + 1) / $daysInMonth * 100;
                            
                            // Calcular posición vertical - altura de cada fila: 46px
                            $topPosition = ($index * 46) + 10; // 10px de margen inicial
                        @endphp
                        
                        <div class="gantt-task-bar" 
                             data-task-id="{{ $task->id }}"
                             data-start-date="{{ $task->finicio ? date('Y-m-d', strtotime($task->finicio)) : '' }}"
                             data-end-date="{{ $task->ftermino ? date('Y-m-d', strtotime($task->ftermino)) : '' }}"
                             style="left: {{ $leftPosition }}%; width: {{ $width }}%; top: {{ $topPosition }}px;">
                            <div class="gantt-task-content">
                                <span class="gantt-task-dates">{{ date('d/m', strtotime($task->finicio)) }} - {{ date('d/m', strtotime($task->ftermino)) }}</span>
                            </div>
                            <div class="gantt-task-resizer gantt-task-resizer-left"></div>
                            <div class="gantt-task-resizer gantt-task-resizer-right"></div>
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
    
    /* Navegación del mes */
    .gantt-month-navigation {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .gantt-nav-btn {
        padding: 6px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        background: #f8f8f8;
        color: #555;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .gantt-nav-btn:hover {
        background: #f0f0f0;
    }
    
    .gantt-current-month {
        font-weight: 500;
        color: #333;
        font-size: 1rem;
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
        // Iniciar arrastre
        taskBar.addEventListener('mousedown', function(e) {
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
    
    // Simplificar el evento mouseup
    document.addEventListener('mouseup', function(e) {
        if (draggingTask) {
            // Finalizar arrastre y actualizar fechas en BD
            updateTaskDatesInDB(draggingTask);
            draggingTask = null;
        }
        
        if (resizing) {
            // Finalizar redimensionamiento y actualizar fechas en BD
            updateTaskDatesInDB(resizing.task);
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
});
</script>
@endsection