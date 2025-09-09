<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AppSetting extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'app_settings';

    /**
     * El tipo de clave primaria.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indica si el ID del modelo es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tab',
        'key',
        'default',
        'value',
    ];

    /**
     * Boot del modelo para generar UUID automáticamente.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Obtiene un valor de configuración por tab y key.
     *
     * @param string $tab
     * @param string $key
     * @return mixed|null
     */
    public static function getSetting(string $tab, string $key)
    {
        $setting = self::where('tab', $tab)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return null;
        }

        return $setting->value ?? $setting->default;
    }

    /**
     * Establece un valor de configuración por tab y key.
     *
     * @param string $tab
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setSetting(string $tab, string $key, $value): bool
    {
        $setting = self::where('tab', $tab)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return false;
        }

        $setting->value = $value;
        return $setting->save();
    }

    /**
     * Determina si un valor debe ser cifrado/descifrado.
     *
     * @param string $tab
     * @param string $key
     * @return bool
     */
    public static function shouldEncrypt(string $tab, string $key): bool
    {
        $encryptedKeys = [
            'FacturacionElectronica.sol_password',
            'FacturacionElectronica.certificate_password',
        ];

        return in_array("$tab.$key", $encryptedKeys);
    }

    /**
     * Obtiene un valor descifrado si es necesario.
     *
     * @param string $tab
     * @param string $key
     * @return mixed|null
     */
    public static function getDecryptedSetting(string $tab, string $key)
    {
        $value = self::getSetting($tab, $key);

        if ($value && self::shouldEncrypt($tab, $key)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                // Si hay un error al descifrar, devolver el valor original
                return $value;
            }
        }

        return $value;
    }

    /**
     * Establece un valor cifrado si es necesario.
     *
     * @param string $tab
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setEncryptedSetting(string $tab, string $key, $value): bool
    {
        if (self::shouldEncrypt($tab, $key)) {
            $value = Crypt::encryptString($value);
        }

        return self::setSetting($tab, $key, $value);
    }

    /**
     * Obtiene el endpoint de QPSE Beta desde Facturación Electrónica.
     *
     * @return string|null
     */
    public static function getQpseEndpointBetaFromFacturacion(): ?string
    {
        return self::getSetting('FacturacionElectronica', 'qpse_endpoint_beta');
    }

    /**
     * Establece el endpoint de QPSE Beta en Facturación Electrónica.
     *
     * @param string $endpoint
     * @return bool
     */
    public static function setQpseEndpointBetaInFacturacion(string $endpoint): bool
    {
        return self::setSetting('FacturacionElectronica', 'qpse_endpoint_beta', $endpoint);
    }

    /**
     * Obtiene el endpoint de QPSE Producción desde Facturación Electrónica.
     *
     * @return string|null
     */
    public static function getQpseEndpointProductionFromFacturacion(): ?string
    {
        return self::getSetting('FacturacionElectronica', 'qpse_endpoint_production');
    }

    /**
     * Establece el endpoint de QPSE Producción en Facturación Electrónica.
     *
     * @param string $endpoint
     * @return bool
     */
    public static function setQpseEndpointProductionInFacturacion(string $endpoint): bool
    {
        return self::setSetting('FacturacionElectronica', 'qpse_endpoint_production', $endpoint);
    }

    /**
     * Obtiene el endpoint de QPSE según el entorno desde Facturación Electrónica.
     *
     * @param string $environment 'beta' o 'production'
     * @return string|null
     */
    public static function getQpseEndpointByEnvironmentFromFacturacion(string $environment): ?string
    {
        return match ($environment) {
            'beta' => self::getQpseEndpointBetaFromFacturacion(),
            'production' => self::getQpseEndpointProductionFromFacturacion(),
            default => null,
        };
    }

    /**
     * Obtiene el usuario QPSE desde Facturación Electrónica.
     *
     * @return string|null
     */
    public static function getQpseUsernameFromFacturacion(): ?string
    {
        return self::getSetting('FacturacionElectronica', 'qpse_username');
    }

    /**
     * Establece el usuario QPSE en Facturación Electrónica.
     *
     * @param string $username
     * @return bool
     */
    public static function setQpseUsernameInFacturacion(string $username): bool
    {
        return self::setSetting('FacturacionElectronica', 'qpse_username', $username);
    }

    /**
     * Obtiene la contraseña QPSE desde Facturación Electrónica (cifrada).
     *
     * @return string|null
     */
    public static function getQpsePasswordFromFacturacion(): ?string
    {
        $encrypted = self::getSetting('FacturacionElectronica', 'qpse_password');
        if ($encrypted) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString($encrypted);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Establece la contraseña QPSE en Facturación Electrónica (se cifra automáticamente).
     *
     * @param string $password
     * @return bool
     */
    public static function setQpsePasswordInFacturacion(string $password): bool
    {
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString($password);
        return self::setSetting('FacturacionElectronica', 'qpse_password', $encrypted);
    }

    /**
     * Obtiene las credenciales completas de QPSE desde Facturación Electrónica.
     *
     * @return array
     */
    public static function getQpseCredentialsFromFacturacion(): array
    {
        return [
            'username' => self::getQpseUsernameFromFacturacion(),
            'password' => self::getQpsePasswordFromFacturacion(),
        ];
    }
}
