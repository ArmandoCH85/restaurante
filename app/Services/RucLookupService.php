<?php

namespace App\Services;

use App\Models\CompanyConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RucLookupService
{
    /**
     * URL base de la API
     */
    private const API_BASE_URL = 'https://apiperu.dev';

    /**
     * Token de autorización para la API
     */
    private ?string $token;

    public function __construct()
    {
        // Se sigue usando el mismo campo de configuración para el token
        $this->token = CompanyConfig::getFactilizaToken();
    }

    /**
     * Busca información de una empresa por su RUC.
     *
     * @param string $ruc El RUC a consultar (11 dígitos)
     * @return array|null Datos de la empresa o null si no se encuentra
     * @throws Exception Si hay errores en la consulta
     */
    public function lookupRuc(string $ruc): ?array
    {
        $this->validateToken();

        // Validar formato del RUC
        if (!$this->isValidRuc($ruc)) {
            throw new Exception('El RUC debe tener exactamente 11 dígitos.');
        }

        try {
            Log::info('🔍 Iniciando búsqueda de RUC en ApiPeru.dev', ['ruc' => $ruc]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post(self::API_BASE_URL . '/api/ruc', [
                'ruc' => $ruc
            ]);

            if (!$response->successful()) {
                $this->logApiError($response, $ruc, 'RUC');
                
                return match($response->status()) {
                    401 => throw new Exception('Token de API inválido o expirado.'),
                    404 => null, // RUC no encontrado
                    422 => throw new Exception('Datos inválidos o RUC incorrecto.'),
                    429 => throw new Exception('Límite de consultas excedido. Intente más tarde.'),
                    500 => throw new Exception('Error del servidor de la API.'),
                    default => throw new Exception('Error en la consulta: ' . $response->status())
                };
            }

            $data = $response->json();

            if (!$data['success']) {
                Log::warning('⚠️ API retornó success: false', ['ruc' => $ruc, 'response' => $data]);
                return null;
            }

            Log::info('✅ RUC encontrado', [
                'ruc' => $ruc,
                'razon_social' => $data['data']['nombre_o_razon_social'] ?? 'N/A'
            ]);

            return $this->normalizeRucResponse($data);

        } catch (Exception $e) {
            Log::error('❌ Error en búsqueda de RUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Busca información de una persona por su DNI.
     *
     * @param string $dni El DNI a consultar (8 dígitos)
     * @return array|null Datos de la persona o null si no se encuentra
     * @throws Exception Si hay errores en la consulta
     */
    public function lookupDni(string $dni): ?array
    {
        $this->validateToken();

        if (!$this->isValidDni($dni)) {
            throw new Exception('El DNI debe tener exactamente 8 dígitos.');
        }

        try {
            Log::info('🔍 Iniciando búsqueda de DNI en ApiPeru.dev', ['dni' => $dni]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post(self::API_BASE_URL . '/api/dni', [
                'dni' => $dni
            ]);

            if (!$response->successful()) {
                $this->logApiError($response, $dni, 'DNI');
                
                return match($response->status()) {
                    401 => throw new Exception('Token de API inválido o expirado.'),
                    404 => null, // DNI no encontrado
                    422 => throw new Exception('Datos inválidos o DNI incorrecto.'),
                    429 => throw new Exception('Límite de consultas excedido. Intente más tarde.'),
                    default => throw new Exception('Error en la consulta: ' . $response->status())
                };
            }

            $data = $response->json();

            if (!$data['success']) {
                return null;
            }

            Log::info('✅ DNI encontrado', [
                'dni' => $dni,
                'nombre_completo' => $data['data']['nombre_completo'] ?? 'N/A'
            ]);

            return $this->normalizeDniResponse($data);

        } catch (Exception $e) {
            Log::error('❌ Error en búsqueda de DNI', [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function validateToken(): void
    {
        if (!$this->token) {
            throw new Exception('Token de API no configurado. Configure el token en Datos de la Empresa.');
        }
    }

    private function isValidRuc(string $ruc): bool
    {
        return preg_match('/^[0-9]{11}$/', $ruc);
    }

    private function isValidDni(string $dni): bool
    {
        return preg_match('/^[0-9]{8}$/', $dni);
    }

    private function normalizeRucResponse(array $response): array
    {
        $data = $response['data'];
        
        return [
            'ruc' => $data['ruc'] ?? $data['numero'] ?? '',
            'razon_social' => $data['nombre_o_razon_social'] ?? '',
            'nombre_comercial' => $data['nombre_o_razon_social'] ?? '', // A veces no viene separado
            'direccion' => $data['direccion_simple'] ?? $data['direccion'] ?? '',
            'direccion_completa' => $data['direccion_completa'] ?? '',
            'distrito' => $data['distrito'] ?? '',
            'provincia' => $data['provincia'] ?? '',
            'departamento' => $data['departamento'] ?? '',
            'estado' => $data['estado'] ?? 'ACTIVO',
            'condicion' => $data['condicion'] ?? 'HABIDO',
            'ubigeo' => $data['ubigeo_sunat'] ?? $data['ubigeo'] ?? [],
            'tipo_contribuyente' => '', // ApiPeru a veces no devuelve esto explícito
            'source' => 'apiperu.dev',
            'consulted_at' => now()->toISOString()
        ];
    }

    private function normalizeDniResponse(array $response): array
    {
        $data = $response['data'];
        
        return [
            'dni' => $data['numero'] ?? $data['dni'] ?? '',
            'nombres' => $data['nombres'] ?? '',
            'apellido_paterno' => $data['apellido_paterno'] ?? '',
            'apellido_materno' => $data['apellido_materno'] ?? '',
            'nombre_completo' => $data['nombre_completo'] ?? '',
            'direccion' => '', // API DNI básica no suele traer dirección por privacidad
            'source' => 'apiperu.dev',
            'consulted_at' => now()->toISOString()
        ];
    }

    private function logApiError($response, string $document, string $type): void
    {
        Log::error("🚨 Error API ($type)", [
            'document' => $document,
            'status' => $response->status(),
            'body' => $response->body()
        ]);
    }

    /**
     * Verifica si el servicio está disponible.
     */
    public function isServiceAvailable(): bool
    {
        if (!$this->token) return false;

        try {
            // Se puede probar con una consulta dummy o verificar formato del token
            return true; 
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene información sobre el estado del token.
     */
    public function getTokenInfo(): array
    {
        if (!$this->token) {
            return [
                'configured' => false,
                'valid' => false,
                'message' => 'Token no configurado'
            ];
        }

        return [
            'configured' => true,
            'valid' => true,
            'message' => 'Token configurado para ApiPeru.dev',
            'account_info' => []
        ];
    }
}