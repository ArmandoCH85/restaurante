<?php

namespace App\Http\Controllers;

use App\Services\RucLookupService;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class RucLookupController extends Controller
{
    private RucLookupService $rucLookupService;

    public function __construct(RucLookupService $rucLookupService)
    {
        $this->rucLookupService = $rucLookupService;
    }

    /**
     * Busca información de una empresa por RUC.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookup(Request $request): JsonResponse
    {
        try {
            // Validar entrada
            $request->validate([
                'ruc' => 'required|string|size:11|regex:/^[0-9]{11}$/'
            ]);

            $ruc = $request->input('ruc');

            Log::info('🔍 Solicitud de búsqueda de RUC', [
                'ruc' => $ruc,
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            // Primero verificar si ya existe en nuestra base de datos
            $existingCustomer = Customer::where('document_number', $ruc)
                ->where('document_type', 'RUC')
                ->first();

            if ($existingCustomer) {
                Log::info('✅ RUC encontrado en base de datos local', [
                    'ruc' => $ruc,
                    'customer_id' => $existingCustomer->id
                ]);

                return response()->json([
                    'success' => true,
                    'source' => 'local_database',
                    'data' => [
                        'ruc' => $existingCustomer->document_number,
                        'razon_social' => $existingCustomer->name,
                        'direccion' => $existingCustomer->address ?? '',
                        'telefono' => $existingCustomer->phone ?? '',
                        'email' => $existingCustomer->email ?? '',
                        'customer_id' => $existingCustomer->id
                    ],
                    'message' => 'Cliente encontrado en base de datos local'
                ]);
            }

            // Si no existe localmente, consultar la API de Factiliza
            $companyData = $this->rucLookupService->lookupRuc($ruc);

            if (!$companyData) {
                Log::warning('❌ RUC no encontrado', ['ruc' => $ruc]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'RUC no encontrado',
                    'message' => 'No se encontró información para el RUC especificado'
                ], 404);
            }

            // Crear automáticamente el cliente en nuestra base de datos
            $newCustomer = $this->createCustomerFromApiData($companyData);

            Log::info('✅ RUC encontrado en Factiliza y cliente creado', [
                'ruc' => $ruc,
                'customer_id' => $newCustomer->id,
                'razon_social' => $companyData['razon_social']
            ]);

            return response()->json([
                'success' => true,
                'source' => 'factiliza_api',
                'data' => array_merge($companyData, [
                    'customer_id' => $newCustomer->id
                ]),
                'message' => 'Información obtenida de Factiliza y cliente creado automáticamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => 'El RUC debe tener exactamente 11 dígitos numéricos',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('❌ Error en búsqueda de RUC', [
                'ruc' => $request->input('ruc'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'service_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca información de una persona por DNI.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookupDni(Request $request): JsonResponse
    {
        try {
            // Validar entrada
            $request->validate([
                'dni' => 'required|string|size:8|regex:/^[0-9]{8}$/'
            ]);

            $dni = $request->input('dni');

            Log::info('🔍 Solicitud de búsqueda de DNI', [
                'dni' => $dni,
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            // Primero verificar si ya existe en nuestra base de datos
            $existingCustomer = Customer::where('document_number', $dni)
                ->where('document_type', 'DNI')
                ->first();

            if ($existingCustomer) {
                Log::info('✅ DNI encontrado en base de datos local', [
                    'dni' => $dni,
                    'customer_id' => $existingCustomer->id
                ]);

                return response()->json([
                    'success' => true,
                    'source' => 'local_database',
                    'data' => [
                        'dni' => $existingCustomer->document_number,
                        'nombre_completo' => $existingCustomer->name,
                        'direccion' => $existingCustomer->address ?? '',
                        'telefono' => $existingCustomer->phone ?? '',
                        'email' => $existingCustomer->email ?? '',
                        'customer_id' => $existingCustomer->id
                    ],
                    'message' => 'Cliente encontrado en base de datos local'
                ]);
            }

            // Si no existe localmente, consultar la API de Factiliza
            $personData = $this->rucLookupService->lookupDni($dni);

            if (!$personData) {
                Log::warning('❌ DNI no encontrado', ['dni' => $dni]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'DNI no encontrado',
                    'message' => 'No se encontró información para el DNI especificado'
                ], 404);
            }

            // Crear automáticamente el cliente en nuestra base de datos
            $newCustomer = $this->createCustomerFromDniData($personData);

            Log::info('✅ DNI encontrado en Factiliza y cliente creado', [
                'dni' => $dni,
                'customer_id' => $newCustomer->id,
                'nombre_completo' => $personData['nombre_completo']
            ]);

            return response()->json([
                'success' => true,
                'source' => 'factiliza_api',
                'data' => array_merge($personData, [
                    'customer_id' => $newCustomer->id
                ]),
                'message' => 'Información obtenida de Factiliza y cliente creado automáticamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'validation_error',
                'message' => 'El DNI debe tener exactamente 8 dígitos numéricos',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('❌ Error en búsqueda de DNI', [
                'dni' => $request->input('dni'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'service_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    {
        try {
            $tokenInfo = $this->rucLookupService->getTokenInfo();
            $isAvailable = $this->rucLookupService->isServiceAvailable();

            return response()->json([
                'success' => true,
                'service_available' => $isAvailable,
                'token_info' => $tokenInfo,
                'message' => $isAvailable ? 'Servicio disponible' : 'Servicio no disponible'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'status_check_failed',
                'message' => 'Error verificando estado del servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca un cliente por RUC en la base de datos local (método existente mejorado).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findCustomer(Request $request): JsonResponse
    {
        try {
            $documentNumber = $request->input('document');
            $documentType = $request->input('type', 'RUC');

            if (!$documentNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Número de documento requerido'
                ], 400);
            }

            // Buscar en base de datos local
            $customer = Customer::where('document_number', $documentNumber)
                ->where('document_type', $documentType)
                ->first();

            if ($customer) {
                return response()->json([
                    'success' => true,
                    'source' => 'local_database',
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'document_number' => $customer->document_number,
                        'document_type' => $customer->document_type,
                        'address' => $customer->address ?? '',
                        'phone' => $customer->phone ?? '',
                        'email' => $customer->email ?? ''
                    ]
                ]);
            }

            // Si es RUC y no se encuentra localmente, sugerir usar API
            if ($documentType === 'RUC' && strlen($documentNumber) === 11) {
                return response()->json([
                    'success' => false,
                    'suggest_api_lookup' => true,
                    'message' => 'Cliente no encontrado. ¿Desea buscar en Factiliza?'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error buscando cliente', [
                'document' => $request->input('document'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea un cliente a partir de los datos de la API.
     *
     * @param array $apiData
     * @return Customer
     */
    private function createCustomerFromApiData(array $apiData): Customer
    {
        return Customer::create([
            'document_type' => 'RUC',
            'document_number' => $apiData['ruc'],
            'name' => $apiData['razon_social'],
            'address' => $this->buildFullAddress($apiData),
            'phone' => $apiData['telefono'] ?? null,
            'email' => $apiData['email'] ?? null,
            'tax_validated' => true, // Marcamos como validado ya que viene de Factiliza
            'address_references' => $this->buildAddressReferences($apiData)
        ]);
    }

    /**
     * Crea un cliente a partir de los datos de DNI de la API.
     *
     * @param array $personData
     * @return Customer
     */
    private function createCustomerFromDniData(array $personData): Customer
    {
        return Customer::create([
            'document_type' => 'DNI',
            'document_number' => $personData['dni'],
            'name' => $personData['nombre_completo'],
            'address' => $this->buildFullAddressFromDni($personData),
            'phone' => $personData['telefono'] ?? null,
            'email' => $personData['email'] ?? null,
            'tax_validated' => true, // Marcamos como validado ya que viene de Factiliza
            'address_references' => $this->buildAddressReferencesFromDni($personData)
        ]);
    }

    /**
     * Construye la dirección completa a partir de los datos de la API.
     *
     * @param array $apiData
     * @return string
     */
    private function buildFullAddress(array $apiData): string
    {
        $addressParts = array_filter([
            $apiData['direccion'] ?? '',
            $apiData['distrito'] ?? '',
            $apiData['provincia'] ?? '',
            $apiData['departamento'] ?? ''
        ]);

        return implode(', ', $addressParts) ?: 'Dirección no especificada';
    }

    /**
     * Construye las referencias de dirección con información adicional.
     *
     * @param array $apiData
     * @return string|null
     */
    private function buildAddressReferences(array $apiData): ?string
    {
        $references = [];

        if (!empty($apiData['estado'])) {
            $references[] = "Estado: {$apiData['estado']}";
        }

        if (!empty($apiData['condicion'])) {
            $references[] = "Condición: {$apiData['condicion']}";
        }

        if (!empty($apiData['actividad_economica'])) {
            $references[] = "Actividad: {$apiData['actividad_economica']}";
        }

        return !empty($references) ? implode(' | ', $references) : null;
    }

    /**
     * Construye la dirección completa a partir de los datos de DNI de la API.
     *
     * @param array $personData
     * @return string
     */
    private function buildFullAddressFromDni(array $personData): string
    {
        $addressParts = array_filter([
            $personData['direccion'] ?? '',
            $personData['distrito'] ?? '',
            $personData['provincia'] ?? '',
            $personData['departamento'] ?? ''
        ]);

        return implode(', ', $addressParts) ?: 'Dirección no especificada';
    }

    /**
     * Construye las referencias de dirección con información adicional de DNI.
     *
     * @param array $personData
     * @return string|null
     */
    private function buildAddressReferencesFromDni(array $personData): ?string
    {
        $references = [];

        if (!empty($personData['sexo'])) {
            $references[] = "Sexo: {$personData['sexo']}";
        }

        if (!empty($personData['fecha_nacimiento'])) {
            $references[] = "F. Nacimiento: {$personData['fecha_nacimiento']}";
        }

        if (!empty($personData['ubigeo_reniec'])) {
            $references[] = "Ubigeo RENIEC: {$personData['ubigeo_reniec']}";
        }

        return !empty($references) ? implode(' | ', $references) : null;
    }
}