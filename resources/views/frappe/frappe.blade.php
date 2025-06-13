<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compromisos - Gantt</title>
    <script src="
    https://cdn.jsdelivr.net/npm/frappe-gantt@1.0.3/dist/frappe-gantt.umd.min.js
    "></script>
    <link href="
    https://cdn.jsdelivr.net/npm/frappe-gantt@1.0.3/dist/frappe-gantt.min.css
    " rel="stylesheet">
</head>
<body>
    <h2>Carta Gantt de Compromisos Activos</h2>
    <div id="gantt"></div>

    <script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                if (typeof Gantt === 'undefined') {
                    console.error('La biblioteca Frappe Gantt no se ha cargado correctamente');
                    document.getElementById('gantt').innerHTML = '<p>Error: No se pudo cargar la biblioteca Gantt</p>';
                    return;
                }
                
                
                // Realizar la petición fetch
                fetch('/api/compromops')
                    .then(response => {
                            console.log('Estado de la respuesta:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos recibidos:', data);
                        
                        if (Array.isArray(data) && data.length > 0) {
                            var gantt = new Gantt("#gantt", data, {
                                language: 'es', 
                                view_mode: 'Day',
                                
                              
                                on_click: task => console.log("Clicked:", task),
                                on_date_change: (task, start, end) => console.log(`${task.name} moved: ${start} -> ${end}`),
                                on_progress_change: (task, progress) => console.log(`${task.name} progreso: ${progress}%`)
                            });
                        } else {
                            document.getElementById('gantt').innerHTML = '<p>No hay tareas disponibles</p>';
                            console.warn('No se recibieron tareas o el formato es incorrecto:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar los datos:', error);
                        document.getElementById('gantt').innerHTML = '<p>Error al cargar las tareas: ' + error.message + '</p>';
                    });
            }, 500);
        });
    </script>
</body>
</html>
