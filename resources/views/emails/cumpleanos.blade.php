{{-- filepath: c:\wamp64\www\example-app2\resources\views\emails\cumpleanos.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Feliz Cumpleaños {{ $nombre }}!</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .email-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
            text-align: center;
            position: relative;
        }
        
        .emojis-header {
            font-size: 2rem;
            margin: 20px 0;
            line-height: 1.2;
        }
        
        .birthday-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            text-transform: uppercase;
        }
        
        .birthday-announcement {
            font-size: 1.3rem;
            color: #495057;
            margin: 30px 0;
            font-weight: 600;
        }
        
        .employee-name {
            color:rgb(0, 0, 0);
            font-weight: 900; /* Más negrita que bold */
            text-transform: uppercase;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1); /* Sombra sutil para más presencia */
            letter-spacing: 0.5px; /* Espaciado para mayor impacto */
        }
        
        .employee-cargo {
            color: #3498db;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .birthday-message {
            font-size: 1.1rem;
            color: #2c3e50;
            margin: 30px 0;
            line-height: 1.6;
            text-align: justify;
            padding: 0 20px;
        }
        
        .employee-name-message {
            color: #e74c3c;
            font-weight: 900; /* Más negrita que bold */
            text-transform: uppercase;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1); /* Sombra sutil */
            letter-spacing: 0.5px; /* Espaciado para mayor impacto */
        }
        
        .logo-container {
            margin: 40px -30px 20px -30px; /* Márgenes negativos para casi tocar los bordes */
            padding-top: 30px;
            border-top: 2px solid #f8f9fa;
        }
        
        .logo {
            max-width: calc(100% - 20px); /* Casi todo el ancho disponible */
            width: 95%; /* 95% del contenedor */
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .footer-space {
            margin-top: 30px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-container {
                padding: 30px 20px;
            }
            
            .emojis-header {
                font-size: 1.5rem;
            }
            
            .birthday-title {
                font-size: 1.4rem;
            }
            
            .birthday-announcement {
                font-size: 1.1rem;
            }
            
            .birthday-message {
                font-size: 1rem;
                padding: 0 10px;
            }
            
            .logo-container {
                margin: 40px -15px 20px -15px; /* Ajuste para móvil */
            }
            
            .logo {
                width: 98%; /* Aún más grande en móvil */
                max-width: calc(100% - 10px);
            }
        }
        
        @media (max-width: 480px) {
            .email-container {
                padding: 20px 15px;
            }
            
            .emojis-header {
                font-size: 1.2rem;
            }
            
            .birthday-title {
                font-size: 1.2rem;
            }
            
            .birthday-announcement {
                font-size: 1rem;
            }
            
            .birthday-message {
                font-size: 0.9rem;
                text-align: left;
            }
            
            .logo-container {
                margin: 30px -10px 15px -10px; /* Ajuste para pantallas muy pequeñas */
            }
            
            .logo {
                width: 99%; /* Máximo posible en pantallas pequeñas */
                max-width: calc(100% - 5px);
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Emojis de encabezado -->
        <div class="emojis-header">
            🎁🎊🎉🍬🎂 ¡Happy Birthday! ¡Feliz Cumpleaños! 🎁🎊🎉🍬🎂
        </div>
        
        <!-- Anuncio del cumpleaños -->
        <div class="birthday-announcement">
            En el día de hoy está de fiesta de cumpleaños: 
            <span class="employee-name">{{ strtoupper($nombre) }}</span>@if($cargo), (<span class="employee-cargo">{{ strtoupper($cargo) }}</span>)@endif
        </div>
        
        <!-- Mensaje personalizado -->
        <div class="birthday-message">
            <span class="employee-name-message">{{ strtoupper($nombre) }}</span>: No se trata de una simple celebración, es el día de tu cumpleaños, es por ello que te deseamos las mejores intenciones en este día.
        </div>
        
        <!-- Logo de INGOMAR - MÁS GRANDE -->
        <div class="logo-container">
            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('images/ingomar-logo.jpg'))) }}" 
                 alt="INGOMAR Logo" 
                 class="logo">
        </div>
        
        <div class="footer-space"></div>
    </div>
</body>
</html>