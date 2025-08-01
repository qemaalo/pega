<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compromisos - Gantt</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@1.0.3/dist/frappe-gantt.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/frappe-gantt@1.0.3/dist/frappe-gantt.min.css" rel="stylesheet">
    <style>
       
        
        .gantt-controls {
            margin-bottom: 15px;
        }
        
        .gantt-controls button {
            background-color: #4a6cf7;
            color: white;
            border: none;
            padding: 10px 18px;
            margin-right: 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 18px;
            border-radius: 6px;
            color: white;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
            font-size: 14px;
            font-weight: 500;
        }
        
        .notification.success { background-color: #4CAF50; }
        .notification.error { background-color: #f44336; }
        .notification.warning { background-color: #ff9800; }
        
        .notification.show { opacity: 1; }
        
        /* Estilos del Modal - Mejorados para mayor tamaño y legibilidad */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: none;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 650px; /* Aumentado de 500px a 650px */
            min-height: 400px; /* Altura mínima */
            animation: slideIn 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .modal-header {
            padding: 28px 32px 20px; /* Aumentado el padding */
            border-bottom: 2px solid #e8eaed; /* Borde más grueso */
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #1a1a1a;
            font-size: 22px; /* Aumentado de 18px */
            font-weight: 600;
            line-height: 1.3;
        }
        
        .modal-body {
            padding: 32px; /* Aumentado de 20px 24px */
        }
        
        .change-info {
            background-color: #f1f3f4;
            padding: 20px; /* Aumentado de 12px */
            border-radius: 8px;
            margin-bottom: 24px; /* Aumentado de 16px */
            font-size: 15px; /* Aumentado de 14px */
            line-height: 1.6; /* Mejor espaciado entre líneas */
            border-left: 4px solid #4a6cf7; /* Borde de color para destacar */
        }
        
        .change-info strong {
            color: #1a1a1a;
            font-weight: 600;
            display: inline-block;
            min-width: 120px; /* Para alinear mejor el contenido */
        }
        
        .change-info div {
            margin-bottom: 8px;
        }
        
        .change-info div:last-child {
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 20px; /* Aumentado de 16px */
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px; /* Aumentado de 6px */
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px; /* Aumentado de 14px */
        }
        
        .form-group textarea {
            width: 100%;
            min-height: 120px; /* Aumentado de 80px */
            padding: 16px; /* Aumentado de 10px */
            border: 2px solid #dadce0; /* Borde más grueso */
            border-radius: 8px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 15px; /* Aumentado de 14px */
            line-height: 1.5;
            resize: vertical;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.2); /* Sombra más prominente */
        }
        
        .form-group textarea::placeholder {
            color: #8a8a8a;
            font-style: italic;
        }
        
        .char-counter {
            text-align: right;
            font-size: 13px;
            color: #666;
            margin-top: 6px;
        }
        
        .modal-footer {
            padding: 24px 32px 32px; /* Aumentado el padding */
            display: flex;
            gap: 16px; /* Aumentado de 12px */
            justify-content: flex-end;
            border-top: 2px solid #e8eaed;
            background-color: #fafafa;
            border-radius: 0 0 12px 12px;
        }
        
        .btn {
            padding: 14px 28px; /* Aumentado de 10px 20px */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px; /* Aumentado de 14px */
            font-weight: 600;
            transition: all 0.2s;
            min-width: 120px; /* Ancho mínimo para botones */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .btn-primary {
            background-color: #4a6cf7;
            color: white;
            box-shadow: 0 2px 8px rgba(74, 108, 247, 0.3);
        }
        
        .btn-primary:hover {
            background-color: #3a5ce5;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 108, 247, 0.4);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
        }
        
        .btn:disabled {
            background-color: #e0e0e0;
            color: #a0a0a0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Mejores animaciones */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-60px) scale(0.95);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Responsive para pantallas pequeñas */
        @media (max-width: 768px) {
            .modal-content {
                max-width: 95%;
                margin: 10px;
            }
            
            .modal-header, .modal-body, .modal-footer {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .btn {
                min-width: 100px;
                padding: 12px 20px;
            }
        }
        
        /* Estilos específicos para tareas inactivas */
        .gantt .bar.task-inactive {
            fill: #bdbdbd !important;
            stroke: #9e9e9e !important;
            opacity: 0.6 !important;
            cursor: not-allowed !important;
        }
        
        .gantt .bar.task-inactive:hover {
            fill: #bdbdbd !important;
            stroke: #9e9e9e !important;
            opacity: 0.6 !important;
        }
        
        .gantt .bar-progress.task-inactive {
            fill: #757575 !important;
        }
        
        .gantt .handle.task-inactive {
            display: none !important;
        }
        
        /* Tooltip para tareas inactivas */
        .gantt .popup-wrapper.task-inactive {
            background-color: #f5f5f5 !important;
            border-left: 4px solid #bdbdbd !important;
        }
        
        .gantt .popup-wrapper.task-inactive .title {
            color: #757575 !important;
        }
    </style>
</head>
<body>
    <h2>Carta Gantt de Compromisos</h2>
    
    <div class="gantt-controls">
        <button onclick="changeView('Day')">Vista Diaria</button>
        <button onclick="changeView('Week')">Vista Semanal</button>
        <button onclick="changeView('Month')">Vista Mensual</button>
        <button onclick="loadTasks()">Actualizar</button>
    </div>
    
    <div id="gantt"></div>
    
    <!-- Modal para comentarios - Mejorado -->
    <div id="commentModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar cambio de fechas</h3>
            </div>
            <div class="modal-body">
                <div id="changeInfo" class="change-info">
                    <!-- Se llenará dinámicamente -->
                </div>
                <div class="form-group">
                    <label for="commentText">Comentario sobre el cambio:</label>
                    <textarea 
                        id="commentText" 
                        placeholder="Describe el motivo del cambio de fechas, impactos, consideraciones especiales, etc..."
                        maxlength="500"
                        oninput="updateCharCounter()"
                    ></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span>/500 caracteres
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cancelChange()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmChange()">
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>
    
    <div id="notification" class="notification"></div>

    <script>
        let ganttChart;
        let debounceTimer;
        let pendingChange = null;
        
        function updateCharCounter() {
            const textarea = document.getElementById('commentText');
            const charCount = document.getElementById('charCount');
            charCount.textContent = textarea.value.length;
            
            // Cambiar color si se acerca al límite
            if (textarea.value.length > 450) {
                charCount.style.color = '#f44336';
            } else if (textarea.value.length > 400) {
                charCount.style.color = '#ff9800';
            } else {
                charCount.style.color = '#666';
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        function showCommentModal(task, start, end) {
            pendingChange = { task: task, start: start, end: end };
            
            const changeInfo = document.getElementById('changeInfo');
            const originalStart = task.start;
            const originalEnd = task.end;
            
            // Formatear las fechas de manera más legible
            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            };
            
            changeInfo.innerHTML = `
                <div><strong>Tarea:</strong> ${task.name}</div>
                <div><strong>Fecha original:</strong> ${formatDate(originalStart)} hasta ${formatDate(originalEnd)}</div>
                <div><strong>Nueva fecha:</strong> ${formatDate(start)} hasta ${formatDate(end)}</div>
            `;
            
            // Limpiar el campo de comentario y contador
            document.getElementById('commentText').value = '';
            updateCharCounter();
            
            // Mostrar el modal
            document.getElementById('commentModal').classList.add('show');
            
            // Enfocar en el textarea después de la animación
            setTimeout(() => {
                document.getElementById('commentText').focus();
            }, 300);
        }
        
        function cancelChange() {
            document.getElementById('commentModal').classList.remove('show');
            loadTasks();
            pendingChange = null;
            showNotification('Cambio cancelado', 'warning');
        }
        
        function confirmChange() {
            if (!pendingChange) {
                showNotification('No hay cambios pendientes', 'error');
                return;
            }
            
            const comment = document.getElementById('commentText').value.trim();
            const { task, start, end } = pendingChange;
            
            const buttons = document.querySelectorAll('.modal-footer .btn');
            buttons.forEach(btn => btn.disabled = true);
            
            saveTaskWithComment(task.id, start, end, comment);
        }
        
        function saveTaskWithComment(taskId, start, end, comment) {
            fetch(`/api/compromops/${taskId}/update-dates`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    start: start,
                    end: end,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Cambios guardados correctamente', 'success');
                    
                    if (pendingChange && pendingChange.task) {
                        pendingChange.task.start = start;
                        pendingChange.task.end = end;
                    }
                    
                    document.getElementById('commentModal').classList.remove('show');
                } else {
                    showNotification('Error: ' + data.message, 'error');
                    loadTasks();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al guardar', 'error');
                loadTasks();
            })
            .finally(() => {
                const buttons = document.querySelectorAll('.modal-footer .btn');
                buttons.forEach(btn => btn.disabled = false);
                pendingChange = null;
            });
        }
        
        function debouncedSave(task, start, end) {
            console.log('debouncedSave llamado:', task.name, start, end);
            
            if (task.readonly) {
                showNotification('Esta tarea está inactiva y no se puede modificar', 'error');
                loadTasks(); // Recargar para restaurar posición original
                return false; // Prevenir el cambio
            }
            
            clearTimeout(debounceTimer);
            
            debounceTimer = setTimeout(() => {
                showCommentModal(task, start, end);
            }, 500);
        }
        
        function loadTasks() {
            clearTimeout(debounceTimer);
            pendingChange = null;
            
            fetch('/api/compromops')
                .then(response => response.json())
                .then(tasks => {
                    console.log('Tareas cargadas:', tasks.length);
                    
                    if (tasks.length > 0) {
                        document.getElementById('gantt').innerHTML = '';
                        
                        ganttChart = new Gantt("#gantt", tasks, {
                            language: 'es',
                            view_mode: 'Week',
                            date_format: 'YYYY-MM-DD',
                            on_date_change: debouncedSave,
                            on_click: function(task) {
                                if (task.readonly) {
                                    showNotification('Tarea inactiva - solo lectura', 'warning');
                                    return false;
                                }
                            },
                            on_progress_change: function(task, progress) {
                                if (task.readonly) {
                                    showNotification('Tarea inactiva - no se puede cambiar el progreso', 'warning');
                                    return false;
                                }
                                console.log('Progreso cambiado:', task.name, progress);
                            },
                            custom_popup_html: function(task) {
                                const status = task.readonly ? 'INACTIVA' : 'ACTIVA';
                                const statusClass = task.readonly ? 'task-inactive' : 'task-active';
                                
                                return `
                                    <div class="popup-wrapper ${statusClass}">
                                        <div class="title">${task.name}</div>
                                        <div class="subtitle">Estado: ${status}</div>
                                        <div class="subtitle">Inicio: ${task.start}</div>
                                        <div class="subtitle">Fin: ${task.end}</div>
                                        <div class="subtitle">Progreso: ${task.progress}%</div>
                                        ${task.readonly ? '<div class="subtitle" style="color: #f44336; font-weight: bold;">⚠️ No editable</div>' : ''}
                                    </div>
                                `;
                            }
                        });
                        
                        console.log('Gantt inicializado correctamente');
                    } else {
                        document.getElementById('gantt').innerHTML = '<p>No hay tareas disponibles</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('gantt').innerHTML = '<p>Error al cargar tareas</p>';
                    showNotification('Error al cargar tareas', 'error');
                });
        }
        
        function changeView(mode) {
            if (ganttChart) {
                ganttChart.change_view_mode(mode);
            }
        }
        
        // Eventos del teclado
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('commentModal');
                if (modal.classList.contains('show')) {
                    cancelChange();
                }
            }
            // Enter para confirmar (solo si no está en el textarea)
            if (event.key === 'Enter' && event.ctrlKey) {
                const modal = document.getElementById('commentModal');
                if (modal.classList.contains('show')) {
                    confirmChange();
                }
            }
        });
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('commentModal').addEventListener('click', function(event) {
            if (event.target === this) {
                cancelChange();
            }
        });
        
        let isInitialized = false;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (isInitialized) return;
            isInitialized = true;
            
            setTimeout(() => {
                if (typeof Gantt !== 'undefined') {
                    loadTasks();
                } else {
                    document.getElementById('gantt').innerHTML = '<p>Error: No se pudo cargar Frappe Gantt</p>';
                }
            }, 500);
        });
    </script>
</body>
</html>
