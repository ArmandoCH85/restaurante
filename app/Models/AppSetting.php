<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
}
