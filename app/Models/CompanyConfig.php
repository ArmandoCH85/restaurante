<?php

namespace App\Models;

use Illuminate\Support\Collection;

class CompanyConfig
{
    /**
     * El tab utilizado para almacenar la configuración de la empresa.
     */
    const TAB = 'Empresa';

    /**
     * Obtiene todos los valores de configuración de la empresa.
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
     * Establece un valor de configuración.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        return AppSetting::setSetting(self::TAB, $key, $value);
    }

    /**
     * Obtiene el RUC de la empresa.
     *
     * @return string|null
     */
    public static function getRuc(): ?string
    {
        return self::get('ruc');
    }

    /**
     * Obtiene la razón social de la empresa.
     *
     * @return string|null
     */
    public static function getRazonSocial(): ?string
    {
        return self::get('razon_social');
    }

    /**
     * Obtiene el nombre comercial de la empresa.
     *
     * @return string|null
     */
    public static function getNombreComercial(): ?string
    {
        return self::get('nombre_comercial');
    }

    /**
     * Obtiene la dirección de la empresa.
     *
     * @return string|null
     */
    public static function getDireccion(): ?string
    {
        return self::get('direccion');
    }

    /**
     * Obtiene el ubigeo de la empresa.
     *
     * @return string|null
     */
    public static function getUbigeo(): ?string
    {
        return self::get('ubigeo');
    }

    /**
     * Obtiene el distrito de la empresa.
     *
     * @return string|null
     */
    public static function getDistrito(): ?string
    {
        return self::get('distrito');
    }

    /**
     * Obtiene la provincia de la empresa.
     *
     * @return string|null
     */
    public static function getProvincia(): ?string
    {
        return self::get('provincia');
    }

    /**
     * Obtiene el departamento de la empresa.
     *
     * @return string|null
     */
    public static function getDepartamento(): ?string
    {
        return self::get('departamento');
    }

    /**
     * Obtiene el código de país de la empresa.
     *
     * @return string|null
     */
    public static function getCodigoPais(): ?string
    {
        return self::get('codigo_pais');
    }

    /**
     * Obtiene el teléfono de la empresa.
     *
     * @return string|null
     */
    public static function getTelefono(): ?string
    {
        return self::get('telefono');
    }

    /**
     * Obtiene el email de la empresa.
     *
     * @return string|null
     */
    public static function getEmail(): ?string
    {
        return self::get('email');
    }

    /**
     * Obtiene el token de la API de Factiliza para búsqueda de RUC.
     *
     * @return string|null
     */
    public static function getFactilizaToken(): ?string
    {
        return self::get('factiliza_token');
    }
}
