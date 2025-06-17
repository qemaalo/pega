<!-- filepath: c:\wamp64\www\example-app2\resources\views\layouts\app.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos base -->
    <style>
        :root {
            --primary-color: #4a6cf7;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
            --success-color: #4caf50;
            --danger-color: #f44336;
            --border-color: #eaeaea;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-header {
            background-color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1300px;
            margin: 0 auto;
        }
        
        .app-logo {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .main-content {
            flex-grow: 1;
            max-width: 1300px;
            margin: 0 auto;
            width: 100%;
            padding: 20px;
        }
        
        .main-footer {
            background-color: white;
            padding: 15px 30px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray-color);
            margin-top: auto;
        }
        
        /* Mensajes flash */
        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="app-container">
 
        
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </main>
        

    </div>
    
    @yield('scripts')
</body>
</html>