<?php

namespace App\Services;

use App\Models\CompanyConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RucLookupService
{
    /**
     * URL base de la API de Factiliza
     */
    private const API_BASE_URL = 'https://api.factiliza.com/v1';

    /**
     * Token de autorización para la API de Factiliza
     */
    private ?string $token;

    public function __construct()
    {
        $this->token = CompanyConfig::getFactilizaToken();
    }

    /**
     * Busca información de una empresa por su RUC usando la API de Factiliza.
     *
     * @param string $ruc El RUC a consultar (11 dígitos)
     * @return array|null Datos de la empresa o null si no se encuentra
     * @throws Exception Si hay errores en la consulta
     */
    public function lookupRuc(string $ruc): ?array
    {
        // Validar que el token esté configurado
        if (!$this->token) {
            throw new Exception('Token de Factiliza no configurado. Configure el token en Datos de la Empresa.');
        }

        // Validar formato del RUC
        if (!$this->isValidRuc($ruc)) {
            throw new Exception('El RUC debe tener exactamente 11 dígitos.');
        }

        try {
            Log::info('🔍 Iniciando búsqueda de RUC en Factiliza', [
                'ruc' => $ruc,
                'api_url' => self::API_BASE_URL . '/ruc/info/' . $ruc,
                'token_length' => strlen($this->token),
                'headers' => [
                    'Authorization' => 'Bearer ' . substr($this->token, 0, 20) . '...',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);

            // Realizar la consulta a la API de Factiliza
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get(self::API_BASE_URL . '/ruc/info/' . $ruc);

            // Verificar si la respuesta fue exitosa
            if (!$response->successful()) {
                $this->logApiError($response, $ruc, 'RUC');
                
                // Manejar diferentes códigos de error
                return match($response->status()) {
                    401 => throw new Exception('Token de Factiliza inválido o expirado.'),
                    404 => null, // RUC no encontrado
                    429 => throw new Exception('Límite de consultas excedido. Intente más tarde.'),
                    default => throw new Exception('Error en la consulta: ' . $response->status())
                };
            }

            $data = $response->json();

            // Verificar que la respuesta contenga datos válidos según el formato de Factiliza
            if (!$this->isValidFactilizaResponse($data)) {
                Log::warning('🚨 Respuesta de API inválida', [
                    'ruc' => $ruc,
                    'response' => $data
                ]);
                return null;
            }

            Log::info('✅ RUC encontrado en Factiliza', [
                'ruc' => $ruc,
                'razon_social' => $data['data']['nombre_o_razon_social'] ?? 'N/A'
            ]);

            // Normalizar y retornar los datos
            return $this->normalizeFactilizaResponse($data);

        } catch (Exception $e) {
            Log::error('❌ Error en búsqueda de RUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Busca información de una persona por su DNI usando la API de Factiliza.
     *
     * @param string $dni El DNI a consultar (8 dígitos)
     * @return array|null Datos de la persona o null si no se encuentra
     * @throws Exception Si hay errores en la consulta
     */
    public function lookupDni(string $dni): ?array
    {
        // Validar que el token esté configurado
        if (!$this->token) {
            throw new Exception('Token de Factiliza no configurado. Configure el token en Datos de la Empresa.');
        }

        // Validar formato del DNI
        if (!$this->isValidDni($dni)) {
            throw new Exception('El DNI debe tener exactamente 8 dígitos.');
        }

        try {
            Log::info('🔍 Iniciando búsqueda de DNI en Factiliza', [
                'dni' => $dni,
                'api_url' => self::API_BASE_URL . '/dni/info/' . $dni,
                'token_length' => strlen($this->token)
            ]);

            // Realizar la consulta a la API de Factiliza
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get(self::API_BASE_URL . '/dni/info/' . $dni);

            // Verificar si la respuesta fue exitosa
            if (!$response->successful()) {
                $this->logApiError($response, $dni, 'DNI');
                
                // Manejar diferentes códigos de error
                return match($response->status()) {
                    401 => throw new Exception('Token de Factiliza inválido o expirado.'),
                    404 => null, // DNI no encontrado
                    429 => throw new Exception('Límite de consultas excedido. Intente más tarde.'),
                    default => throw new Exception('Error en la consulta: ' . $response->status())
                };
            }

            $data = $response->json();

            // Verificar que la respuesta contenga datos válidos según el formato de Factiliza
            if (!$this->isValidFactilizaDniResponse($data)) {
                Log::warning('🚨 Respuesta de API DNI inválida', [
                    'dni' => $dni,
                    'response' => $data
                ]);
                return null;
            }

            Log::info('✅ DNI encontrado en Factiliza', [
                'dni' => $dni,
                'nombre_completo' => $data['data']['nombre_completo'] ?? 'N/A'
            ]);

            // Normalizar y retornar los datos
            return $this->normalizeFactilizaDniResponse($data);

        } catch (Exception $e) {
            Log::error('❌ Error en búsqueda de DNI', [
                'dni' => $dni,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Valida que la respuesta de la API contenga los campos necesarios.
     */
    private function isValidApiResponse(array $data): bool
    {
        return isset($data['ruc']) && isset($data['razon_social']);
    }

    /**
     * Valida que la respuesta de Factiliza contenga los campos necesarios.
     */
    private function isValidFactilizaResponse(array $data): bool
    {
        return isset($data['success']) && 
               $data['success'] === true && 
               isset($data['data']) && 
               isset($data['data']['numero']) && 
               isset($data['data']['nombre_o_razon_social']);
    }

    /**
     * Valida el formato del RUC.
     */
    private function isValidRuc(string $ruc): bool
    {
        return preg_match('/^[0-9]{11}$/', $ruc);
    }

    /**
     * Valida el formato del DNI.
     */
    private function isValidDni(string $dni): bool
    {
        return preg_match('/^[0-9]{8}$/', $dni);
    }

    /**
     * Valida que la respuesta de Factiliza DNI contenga los campos necesarios.
     */
    private function isValidFactilizaDniResponse(array $data): bool
    {
        return isset($data['success']) && 
               $data['success'] === true && 
               isset($data['data']) && 
               isset($data['data']['numero']) && 
               isset($data['data']['nombre_completo']);
    }

    /**
     * Normaliza la respuesta de Factiliza DNI a un formato estándar.
     */
    private function normalizeFactilizaDniResponse(array $response): array
    {
        $data = $response['data'];
        
        return [
            'dni' => $data['numero'] ?? '',
            'nombres' => $data['nombres'] ?? '',
            'apellido_paterno' => $data['apellido_paterno'] ?? '',
            'apellido_materno' => $data['apellido_materno'] ?? '',
            'nombre_completo' => $data['nombre_completo'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'direccion_completa' => $data['direccion_completa'] ?? '',
            'distrito' => $data['distrito'] ?? '',
            'provincia' => $data['provincia'] ?? '',
            'departamento' => $data['departamento'] ?? '',
            'ubigeo_reniec' => $data['ubigeo_reniec'] ?? '',
            'ubigeo_sunat' => $data['ubigeo_sunat'] ?? '',
            'ubigeo_array' => $data['ubigeo'] ?? [],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? '',
            'sexo' => $data['sexo'] ?? '',
            'telefono' => '', // No viene en la API de Factiliza
            'email' => '', // No viene en la API de Factiliza
            'source' => 'factiliza',
            'consulted_at' => now()->toISOString()
        ];
    }

    /**
     * Normaliza la respuesta de Factiliza a un formato estándar.
     */
    private function normalizeFactilizaResponse(array $response): array
    {
        $data = $response['data'];
        
        return [
            'ruc' => $data['numero'] ?? '',
            'razon_social' => $data['nombre_o_razon_social'] ?? '',
            'nombre_comercial' => $data['nombre_o_razon_social'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'direccion_completa' => $data['direccion_completa'] ?? '',
            'distrito' => $data['distrito'] ?? '',
            'provincia' => $data['provincia'] ?? '',
            'departamento' => $data['departamento'] ?? '',
            'estado' => $data['estado'] ?? 'ACTIVO',
            'condicion' => $data['condicion'] ?? 'HABIDO',
            'ubigeo' => $data['ubigeo_sunat'] ?? '',
            'ubigeo_array' => $data['ubigeo'] ?? [],
            'tipo_contribuyente' => $data['tipo_contribuyente'] ?? '',
            'telefono' => '', // No viene en la API de Factiliza
            'email' => '', // No viene en la API de Factiliza
            'fecha_inscripcion' => null,
            'actividad_economica' => '',
            'sistema_emision' => '',
            'actividades_secundarias' => [],
            'comprobantes_emision' => [],
            'sistema_contabilidad' => '',
            'comercio_exterior' => '',
            'source' => 'factiliza',
            'consulted_at' => now()->toISOString()
        ];
    }

    /**
     * Registra errores de la API en los logs.
     */
    private function logApiError($response, string $document, string $type = 'RUC'): void
    {
        Log::error('🚨 Error en API de Factiliza (' . $type . ')', [
            strtolower($type) => $document,
            'status' => $response->status(),
            'response_body' => $response->body(),
            'headers' => $response->headers()
        ]);
    }

    /**
     * Verifica si el servicio está disponible.
     */
    public function isServiceAvailable(): bool
    {
        if (!$this->token) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/json'
                ])
                ->get(self::API_BASE_URL . '/status');

            return $response->successful();
        } catch (Exception $e) {
            Log::warning('🔧 Servicio de Factiliza no disponible', [
                'error' => $e->getMessage()
            ]);
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

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/json'
                ])
                ->get(self::API_BASE_URL . '/account');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'configured' => true,
                    'valid' => true,
                    'message' => 'Token válido',
                    'account_info' => $data
                ];
            } else {
                return [
                    'configured' => true,
                    'valid' => false,
                    'message' => 'Token inválido o expirado'
                ];
            }
        } catch (Exception $e) {
            return [
                'configured' => true,
                'valid' => false,
                'message' => 'Error verificando token: ' . $e->getMessage()
            ];
        }
    }
}