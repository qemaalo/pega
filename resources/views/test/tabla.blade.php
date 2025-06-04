<!-- filepath: c:\wamp64\www\example-app2\resources\views\test\tabla.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de ejemplo</title>
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: auto;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1500px;
        }
        
        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            min-width: 120px;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tabla de ejemplo </h2>
        
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
                    <tr>
                        <td>Dato 2,1</td>
                        <td>Dato 2,2</td>
                        <td>Dato 2,3</td>
                        <td>Dato 2,4</td>
                        <td>Dato 2,5</td>
                        <td>Dato 2,6</td>
                        <td>Dato 2,7</td>
                        <td>Dato 2,8</td>
                        <td>Dato 2,9</td>
                        <td>Dato 2,10</td>
                        <td>Dato 2,11</td>
                        <td>Dato 2,12</td>
                        <td>Dato 2,13</td>
                        <td>Dato 2,14</td>
                        <td>Dato 2,15</td>
                        <td>Dato 2,16</td>
                        <td>Dato 2,17</td>
                        <td>Dato 2,18</td>
                        <td>Dato 2,19</td>
                        <td>Dato 2,20</td>
                    </tr>
                    <tr>
                        <td>Dato 3,1</td>
                        <td>Dato 3,2</td>
                        <td>Dato 3,3</td>
                        <td>Dato 3,4</td>
                        <td>Dato 3,5</td>
                        <td>Dato 3,6</td>
                        <td>Dato 3,7</td>
                        <td>Dato 3,8</td>
                        <td>Dato 3,9</td>
                        <td>Dato 3,10</td>
                        <td>Dato 3,11</td>
                        <td>Dato 3,12</td>
                        <td>Dato 3,13</td>
                        <td>Dato 3,14</td>
                        <td>Dato 3,15</td>
                        <td>Dato 3,16</td>
                        <td>Dato 3,17</td>
                        <td>Dato 3,18</td>
                        <td>Dato 3,19</td>
                        <td>Dato 3,20</td>
                    </tr>
                    <tr>
                        <td>Dato 4,1</td>
                        <td>Dato 4,2</td>
                        <td>Dato 4,3</td>
                        <td>Dato 4,4</td>
                        <td>Dato 4,5</td>
                        <td>Dato 4,6</td>
                        <td>Dato 4,7</td>
                        <td>Dato 4,8</td>
                        <td>Dato 4,9</td>
                        <td>Dato 4,10</td>
                        <td>Dato 4,11</td>
                        <td>Dato 4,12</td>
                        <td>Dato 4,13</td>
                        <td>Dato 4,14</td>
                        <td>Dato 4,15</td>
                        <td>Dato 4,16</td>
                        <td>Dato 4,17</td>
                        <td>Dato 4,18</td>
                        <td>Dato 4,19</td>
                        <td>Dato 4,20</td>
                    </tr>
                    <tr>
                        <td>Dato 5,1</td>
                        <td>Dato 5,2</td>
                        <td>Dato 5,3</td>
                        <td>Dato 5,4</td>
                        <td>Dato 5,5</td>
                        <td>Dato 5,6</td>
                        <td>Dato 5,7</td>
                        <td>Dato 5,8</td>
                        <td>Dato 5,9</td>
                        <td>Dato 5,10</td>
                        <td>Dato 5,11</td>
                        <td>Dato 5,12</td>
                        <td>Dato 5,13</td>
                        <td>Dato 5,14</td>
                        <td>Dato 5,15</td>
                        <td>Dato 5,16</td>
                        <td>Dato 5,17</td>
                        <td>Dato 5,18</td>
                        <td>Dato 5,19</td>
                        <td>Dato 5,20</td>
                    </tr>
                    <tr>
                        <td>Dato 6,1</td>
                        <td>Dato 6,2</td>
                        <td>Dato 6,3</td>
                        <td>Dato 6,4</td>
                        <td>Dato 6,5</td>
                        <td>Dato 6,6</td>
                        <td>Dato 6,7</td>
                        <td>Dato 6,8</td>
                        <td>Dato 6,9</td>
                        <td>Dato 6,10</td>
                        <td>Dato 6,11</td>
                        <td>Dato 6,12</td>
                        <td>Dato 6,13</td>
                        <td>Dato 6,14</td>
                        <td>Dato 6,15</td>
                        <td>Dato 6,16</td>
                        <td>Dato 6,17</td>
                        <td>Dato 6,18</td>
                        <td>Dato 6,19</td>
                        <td>Dato 6,20</td>
                    </tr>
                    <tr>
                        <td>Dato 7,1</td>
                        <td>Dato 7,2</td>
                        <td>Dato 7,3</td>
                        <td>Dato 7,4</td>
                        <td>Dato 7,5</td>
                        <td>Dato 7,6</td>
                        <td>Dato 7,7</td>
                        <td>Dato 7,8</td>
                        <td>Dato 7,9</td>
                        <td>Dato 7,10</td>
                        <td>Dato 7,11</td>
                        <td>Dato 7,12</td>
                        <td>Dato 7,13</td>
                        <td>Dato 7,14</td>
                        <td>Dato 7,15</td>
                        <td>Dato 7,16</td>
                        <td>Dato 7,17</td>
                        <td>Dato 7,18</td>
                        <td>Dato 7,19</td>
                        <td>Dato 7,20</td>
                    </tr>
                    <tr>
                        <td>Dato 8,1</td>
                        <td>Dato 8,2</td>
                        <td>Dato 8,3</td>
                        <td>Dato 8,4</td>
                        <td>Dato 8,5</td>
                        <td>Dato 8,6</td>
                        <td>Dato 8,7</td>
                        <td>Dato 8,8</td>
                        <td>Dato 8,9</td>
                        <td>Dato 8,10</td>
                        <td>Dato 8,11</td>
                        <td>Dato 8,12</td>
                        <td>Dato 8,13</td>
                        <td>Dato 8,14</td>
                        <td>Dato 8,15</td>
                        <td>Dato 8,16</td>
                        <td>Dato 8,17</td>
                        <td>Dato 8,18</td>
                        <td>Dato 8,19</td>
                        <td>Dato 8,20</td>
                    </tr>
                    <tr>
                        <td>Dato 9,1</td>
                        <td>Dato 9,2</td>
                        <td>Dato 9,3</td>
                        <td>Dato 9,4</td>
                        <td>Dato 9,5</td>
                        <td>Dato 9,6</td>
                        <td>Dato 9,7</td>
                        <td>Dato 9,8</td>
                        <td>Dato 9,9</td>
                        <td>Dato 9,10</td>
                        <td>Dato 9,11</td>
                        <td>Dato 9,12</td>
                        <td>Dato 9,13</td>
                        <td>Dato 9,14</td>
                        <td>Dato 9,15</td>
                        <td>Dato 9,16</td>
                        <td>Dato 9,17</td>
                        <td>Dato 9,18</td>
                        <td>Dato 9,19</td>
                        <td>Dato 9,20</td>
                    </tr>
                    <tr>
                        <td>Dato 10,1</td>
                        <td>Dato 10,2</td>
                        <td>Dato 10,3</td>
                        <td>Dato 10,4</td>
                        <td>Dato 10,5</td>
                        <td>Dato 10,6</td>
                        <td>Dato 10,7</td>
                        <td>Dato 10,8</td>
                        <td>Dato 10,9</td>
                        <td>Dato 10,10</td>
                        <td>Dato 10,11</td>
                        <td>Dato 10,12</td>
                        <td>Dato 10,13</td>
                        <td>Dato 10,14</td>
                        <td>Dato 10,15</td>
                        <td>Dato 10,16</td>
                        <td>Dato 10,17</td>
                        <td>Dato 10,18</td>
                        <td>Dato 10,19</td>
                        <td>Dato 10,20</td>
                    </tr>
                    <tr>
                        <td>Dato 11,1</td>
                        <td>Dato 11,2</td>
                        <td>Dato 11,3</td>
                        <td>Dato 11,4</td>
                        <td>Dato 11,5</td>
                        <td>Dato 11,6</td>
                        <td>Dato 11,7</td>
                        <td>Dato 11,8</td>
                        <td>Dato 11,9</td>
                        <td>Dato 11,10</td>
                        <td>Dato 11,11</td>
                        <td>Dato 11,12</td>
                        <td>Dato 11,13</td>
                        <td>Dato 11,14</td>
                        <td>Dato 11,15</td>
                        <td>Dato 11,16</td>
                        <td>Dato 11,17</td>
                        <td>Dato 11,18</td>
                        <td>Dato 11,19</td>
                        <td>Dato 11,20</td>
                    </tr>
                    <tr>
                        <td>Dato 12,1</td>
                        <td>Dato 12,2</td>
                        <td>Dato 12,3</td>
                        <td>Dato 12,4</td>
                        <td>Dato 12,5</td>
                        <td>Dato 12,6</td>
                        <td>Dato 12,7</td>
                        <td>Dato 12,8</td>
                        <td>Dato 12,9</td>
                        <td>Dato 12,10</td>
                        <td>Dato 12,11</td>
                        <td>Dato 12,12</td>
                        <td>Dato 12,13</td>
                        <td>Dato 12,14</td>
                        <td>Dato 12,15</td>
                        <td>Dato 12,16</td>
                        <td>Dato 12,17</td>
                        <td>Dato 12,18</td>
                        <td>Dato 12,19</td>
                        <td>Dato 12,20</td>
                    </tr>
                    <tr>
                        <td>Dato 13,1</td>
                        <td>Dato 13,2</td>
                        <td>Dato 13,3</td>
                        <td>Dato 13,4</td>
                        <td>Dato 13,5</td>
                        <td>Dato 13,6</td>
                        <td>Dato 13,7</td>
                        <td>Dato 13,8</td>
                        <td>Dato 13,9</td>
                        <td>Dato 13,10</td>
                        <td>Dato 13,11</td>
                        <td>Dato 13,12</td>
                        <td>Dato 13,13</td>
                        <td>Dato 13,14</td>
                        <td>Dato 13,15</td>
                        <td>Dato 13,16</td>
                        <td>Dato 13,17</td>
                        <td>Dato 13,18</td>
                        <td>Dato 13,19</td>
                        <td>Dato 13,20</td>
                    </tr>
                    <tr>
                        <td>Dato 14,1</td>
                        <td>Dato 14,2</td>
                        <td>Dato 14,3</td>
                        <td>Dato 14,4</td>
                        <td>Dato 14,5</td>
                        <td>Dato 14,6</td>
                        <td>Dato 14,7</td>
                        <td>Dato 14,8</td>
                        <td>Dato 14,9</td>
                        <td>Dato 14,10</td>
                        <td>Dato 14,11</td>
                        <td>Dato 14,12</td>
                        <td>Dato 14,13</td>
                        <td>Dato 14,14</td>
                        <td>Dato 14,15</td>
                        <td>Dato 14,16</td>
                        <td>Dato 14,17</td>
                        <td>Dato 14,18</td>
                        <td>Dato 14,19</td>
                        <td>Dato 14,20</td>
                    </tr>
                    <tr>
                        <td>Dato 15,1</td>
                        <td>Dato 15,2</td>
                        <td>Dato 15,3</td>
                        <td>Dato 15,4</td>
                        <td>Dato 15,5</td>
                        <td>Dato 15,6</td>
                        <td>Dato 15,7</td>
                        <td>Dato 15,8</td>
                        <td>Dato 15,9</td>
                        <td>Dato 15,10</td>
                        <td>Dato 15,11</td>
                        <td>Dato 15,12</td>
                        <td>Dato 15,13</td>
                        <td>Dato 15,14</td>
                        <td>Dato 15,15</td>
                        <td>Dato 15,16</td>
                        <td>Dato 15,17</td>
                        <td>Dato 15,18</td>
                        <td>Dato 15,19</td>
                        <td>Dato 15,20</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>