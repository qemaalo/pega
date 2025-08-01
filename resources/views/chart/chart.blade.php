<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gantt Chart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/jsgantt-improved@2.0.2/dist/jsgantt.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/jsgantt-improved@2.0.2/dist/jsgantt.min.js" type="text/javascript"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        h2 {
            color: #333;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
        }
        
        #GanttChartDIV {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            background-color: white;
            min-height: 400px;
        }
        
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            font-size: 16px;
            color: #6c757d;
        }
        
        .loading::after {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid #dee2e6;
            border-top: 2px solid #4a6cf7;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gantt Chart Example</h2>
        
        <div id="GanttChartDIV">
            <div class="loading">Loading tasks...</div>
        </div>
    </div>

    <script>
        let g;

        function loadTasks() {
            const ganttDiv = document.getElementById('GanttChartDIV');
            ganttDiv.innerHTML = '<div class="loading">Loading tasks...</div>';
            
            fetch('/api/chart/tasks')
                .then(response => response.json())
                .then(tasks => {
                    initializeGantt(tasks);
                })
                .catch(error => {
                    console.error('Error loading tasks:', error);
                    ganttDiv.innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;">Error loading tasks</div>';
                });
        }

        function initializeGantt(tasks) {
            const ganttDiv = document.getElementById('GanttChartDIV');
            ganttDiv.innerHTML = '';
            
            g = new JSGantt.GanttChart(ganttDiv, 'Week');
            
            tasks.forEach(task => {
                g.AddTaskItemObject({
                    pID: task.id,
                    pName: task.name,
                    pStart: task.start,
                    pEnd: task.end,
                    pClass: task.class,
                    pLink: task.link || "",
                    pMile: task.mile || 0,
                    pRes: task.resource,
                    pComp: task.completion,
                    pGroup: task.group || 0,
                    pParent: task.parent || 0,
                    pOpen: task.open || 1,
                    pDepend: task.depend || "",
                    pCaption: task.caption || "",
                    pCost: 0,
                    pNotes: task.notes
                });
            });
            
            g.Draw();
        }

        document.addEventListener('DOMContentLoaded', loadTasks);
    </script>
</body>
</html>