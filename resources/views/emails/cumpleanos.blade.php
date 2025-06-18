{{-- filepath: c:\wamp64\www\example-app2\resources\views\emails\cumpleanos.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cumpleaños de {{ $nombre }}</title>
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
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .birthday-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 1.8rem;
        }
        
        .birthday-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 25px 0;
        }
        
        .birthday-card h2 {
            margin: 0 0 15px 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .birthday-card .age {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 15px 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .birthday-card .message {
            font-size: 1.2rem;
            margin-top: 15px;
            opacity: 0.95;
        }
        
        .details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
        }
        
        .details h3 {
            margin-top: 0;
            color: #495057;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        
        .detail-value {
            color: #6c757d;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-empresa {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .badge-externo {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        
        .celebration {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
            border: 2px solid #fdcb6e;
        }
        
        .celebration h3 {
            margin: 0 0 10px 0;
            color: #2d3436;
            font-size: 1.3rem;
        }
        
        .celebration p {
            margin: 0;
            color: #636e72;
            font-size: 1.1rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .emoji {
            font-size: 1.2rem;
            margin: 0 5px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-container {
                padding: 20px;
            }
            
            .birthday-card {
                padding: 20px;
            }
            
            .birthday-card h2 {
                font-size: 1.5rem;
            }
            
            .birthday-card .age {
                font-size: 2rem;
            }
            
            .details {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <span class="birthday-icon">🎉</span>
            <h1>¡Cumpleaños de Hoy!</h1>
        </div>
        
        <div class="birthday-card">
            <h2>{{ $nombre }}</h2>
            <div class="age">{{ $edad }} años</div>
            <div class="message">¡Feliz Cumpleaños! <span class="emoji">🎊</span></div>
        </div>
        
        <div class="celebration">
            <h3>🎁 ¡No olvides felicitar a {{ explode(' ', $nombre)[0] }}!</h3>
            <p>Hoy es un día especial para celebrar</p>
        </div>
        
        <div class="details">
            <h3>📋 Información del Cumpleañero</h3>
            
            <div class="detail-item">
                <span class="detail-label">Nombre completo:</span>
                <span class="detail-value">{{ $nombre }}</span>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Fecha de nacimiento:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($fechaCumpleanos)->format('d/m/Y') }}</span>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Edad que cumple:</span>
                <span class="detail-value">{{ $edad }} años</span>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Vinculación:</span>
                <span class="detail-value">
                    @if($vinculadoEmpresa)
                        <span class="badge badge-empresa">Empleado de la empresa</span>
                    @else
                        <span class="badge badge-externo">Persona externa</span>
                    @endif
                </span>
            </div>
        </div>
        
        <div class="footer">
            <p>
                <span class="emoji">🤖</span> 
                Recordatorio automático enviado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}
                <br>
                Sistema de Gestión de Cumpleaños
            </p>
        </div>
    </div>
</body>
</html>