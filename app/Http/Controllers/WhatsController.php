<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsController extends Controller
{
    /**
     * Enviar WhatsApp con cURL (método original mejorado)
     */
    public function enviaWhatsApp(Request $request)
    {
        // Verificar autenticación
        if (session('BODEGAYY') != 'BODEGAYY' && session('BODEGAYN') != 'BODEGAYN') {
            session()->flash('accesoDenegado', 'Acceso Denegado!!!');       
            return redirect()->to('/');
        }

        try {
            // Configuración
            $token = 'EAARqeMXHcVEBO43ZCgA3ZCCpPLsnJZCgJ0YjRZA72GgQ4ho9ormn6ZA5gn3kIc0QOcDq0ZCRAy19qxWHmkZAb5DJbIXqirZBbcZBGZACV1E5Dzo06G6jnagqlQNJrGxbAlIQgyOcmhYMGTDQxpOPltf2pDyBz0UVxfza29VY7PnH0s9dKsH9MQV7qdNgt2ZBPmf9vADtzeisGW1QDNbFaykxWXZBrXQogkNbWhwPjHgZD';
            $telefono = $request->input('telefono', '56973086092');
            $url = 'https://graph.facebook.com/v21.0/423042564235337/messages';

            // Construir mensaje JSON
            $mensaje = json_encode([
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'template',
                'template' => [
                    'name' => 'hello_world',
                    'language' => [
                        'code' => 'en_US'
                    ]
                ]
            ]);

            // Configurar cURL
            $header = [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json"
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);

            // Ejecutar petición
            $response_raw = curl_exec($curl);
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            // Verificar errores de cURL
            if ($error) {
                throw new \Exception("Error cURL: " . $error);
            }

            // Decodificar respuesta
            $response = json_decode($response_raw, true);

            // Log para debugging
            Log::info('WhatsApp Response', [
                'status_code' => $status_code,
                'response' => $response,
                'telefono' => $telefono
            ]);

            // Responder según el resultado
            if ($status_code >= 200 && $status_code < 300) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado correctamente',
                    'data' => $response,
                    'telefono' => $telefono,
                    'status_code' => $status_code
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar mensaje',
                    'data' => $response,
                    'status_code' => $status_code
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error en enviaWhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar WhatsApp con HTTP Client de Laravel
     */
    public function enviarWhatsApp(Request $request)
    {
        // Verificar autenticación
        if (session('BODEGAYY') != 'BODEGAYY' && session('BODEGAYN') != 'BODEGAYN') {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado'
            ], 403);
        }

        try {
            $url = env('WHATSAPP_API_URL', 'https://graph.facebook.com/v21.0/423042564235337/messages');
            $accessToken = env('WHATSAPP_ACCESS_TOKEN', 'EAARqeMXHcVEBO43ZCgA3ZCCpPLsnJZCgJ0YjRZA72GgQ4ho9ormn6ZA5gn3kIc0QOcDq0ZCRAy19qxWHmkZAb5DJbIXqirZBbcZBGZACV1E5Dzo06G6jnagqlQNJrGxbAlIQgyOcmhYMGTDQxpOPltf2pDyBz0UVxfza29VY7PnH0s9dKsH9MQV7qdNgt2ZBPmf9vADtzeisGW1QDNbFaykxWXZBrXQogkNbWhwPjHgZD');
            $telefono = $request->input('telefono', '56973086092');

            $body = [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'template',
                'template' => [
                    'name' => 'hello_world',
                    'language' => [
                        'code' => 'en_US'
                    ]
                ]
            ];

            $response = Http::timeout(30)
                ->withToken($accessToken)
                ->post($url, $body);

            Log::info('WhatsApp HTTP Response', [
                'status' => $response->status(),
                'response' => $response->json(),
                'telefono' => $telefono
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado correctamente',
                    'data' => $response->json(),
                    'telefono' => $telefono
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar mensaje',
                    'data' => $response->json(),
                    'status' => $response->status()
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Error en enviarWhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
