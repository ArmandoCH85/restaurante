<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class ElectronicBillingConfig
{
    /**
     * El tab utilizado para almacenar la configuración de facturación electrónica.
     */
    const TAB = 'FacturacionElectronica';

    /**
     * Obtiene todos los valores de configuración de facturación electrónica.
     *
     * @return Collection
     */
    public static function all(): Collection
    {
        return AppSetting::where('tab', self::TAB)->get();
    }

    /**
     * Obtiene un valor de configuración específico.
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key)
    {
        return AppSetting::getSetting(self::TAB, $key);
    }

    /**
     * Obtiene un valor de configuración descifrado si es necesario.
     *
     * @param string $key
     * @return mixed|null
     */
    public static function getDecrypted(string $key)
    {
        return AppSetting::getDecryptedSetting(self::TAB, $key);
    }

    /**
     * Establece un valor de configuración.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        return AppSetting::setEncryptedSetting(self::TAB, $key, $value);
    }

    /**
     * Obtiene el tipo de conexión SOAP.
     *
     * @return string|null
     */
    public static function getSoapType(): ?string
    {
        return self::get('soap_type');
    }

    /**
     * Obtiene el entorno de SUNAT.
     *
     * @return string|null
     */
    public static function getEnvironment(): ?string
    {
        return self::get('environment');
    }

    /**
     * Obtiene el usuario secundario SOL.
     *
     * @return string|null
     */
    public static function getSolUser(): ?string
    {
        return self::get('sol_user');
    }

    /**
     * Obtiene la contraseña SOL descifrada.
     *
     * @return string|null
     */
    public static function getSolPassword(): ?string
    {
        return self::getDecrypted('sol_password');
    }

    /**
     * Obtiene la ruta al certificado digital.
     *
     * @return string|null
     */
    public static function getCertificatePath(): ?string
    {
        return self::get('certificate_path');
    }

    /**
     * Obtiene la contraseña del certificado descifrada.
     *
     * @return string|null
     */
    public static function getCertificatePassword(): ?string
    {
        return self::getDecrypted('certificate_password');
    }

    /**
     * Verifica si los comprobantes se envían automáticamente.
     *
     * @return bool
     */
    public static function getSendAutomatically(): bool
    {
        return self::get('send_automatically') === 'true';
    }

    /**
     * Verifica si se generan PDFs automáticamente.
     *
     * @return bool
     */
    public static function getGeneratePdf(): bool
    {
        return self::get('generate_pdf') === 'true';
    }

    /**
     * Obtiene el porcentaje de IGV.
     *
     * @return float
     */
    public static function getIgvPercent(): float
    {
        return (float) self::get('igv_percent');
    }
}
