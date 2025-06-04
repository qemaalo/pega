<!-- filepath: c:\wamp64\www\example-app2\resources\views\test\tabla2.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de ejemplo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 20px;
        }
        
        h2 {
            font-size: 20px;
            margin-top: 0;
            color: #444;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .table-container {
            max-height: 350px;
            overflow-y: auto;
            overflow-x: auto;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #eaeaea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
            font-size: 13px;
        }
        
        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-right: 1px solid #eaeaea;
            min-width: 100px;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        th:last-child, td:last-child {
            border-right: none;
        }
        
        th {
            background-color: #f7f7f7;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
        }
        
        tr:nth-child(even) {
            background-color: #fbfbfb;
        }
        
        tr:hover {
            background-color: #f0f7ff;
            transition: background-color 0.2s ease;
        }
        
        /* Estilizar scrollbar para navegadores webkit */
        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .table-container::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 4px;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover {
            background: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tabla de datos</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Columna 1</th>
                        <th>Columna 2</th>
                        <th>Columna 3</th>
                        <th>Columna 4</th>
                        <th>Columna 5</th>
                        <th>Columna 6</th>
                        <th>Columna 7</th>
                        <th>Columna 8</th>
                        <th>Columna 9</th>
                        <th>Columna 10</th>
                        <th>Columna 11</th>
                        <th>Columna 12</th>
                        <th>Columna 13</th>
                        <th>Columna 14</th>
                        <th>Columna 15</th>
                        <th>Columna 16</th>
                        <th>Columna 17</th>
                        <th>Columna 18</th>
                        <th>Columna 19</th>
                        <th>Columna 20</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dato 1,1</td>
                        <td>Dato 1,2</td>
                        <td>Dato 1,3</td>
                        <td>Dato 1,4</td>
                        <td>Dato 1,5</td>
                        <td>Dato 1,6</td>
                        <td>Dato 1,7</td>
                        <td>Dato 1,8</td>
                        <td>Dato 1,9</td>
                        <td>Dato 1,10</td>
                        <td>Dato 1,11</td>
                        <td>Dato 1,12</td>
                        <td>Dato 1,13</td>
                        <td>Dato 1,14</td>
                        <td>Dato 1,15</td>
                        <td>Dato 1,16</td>
                        <td>Dato 1,17</td>
                        <td>Dato 1,18</td>
                        <td>Dato 1,19</td>
                        <td>Dato 1,20</td>
                    </tr>
                    <!-- Filas adicionales omitidas por brevedad -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>