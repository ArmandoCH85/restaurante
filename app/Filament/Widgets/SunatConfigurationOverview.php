<?php

namespace App\Filament\Widgets;

use App\Models\AppSetting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SunatConfigurationOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s'; // Intervalo ajustado para reducir carga

    protected function getStats(): array
    {
        $environment = AppSetting::getSetting('FacturacionElectronica', 'environment');
        $certificatePath = AppSetting::getSetting('FacturacionElectronica', 'certificate_path');
        $solUser = AppSetting::getSetting('FacturacionElectronica', 'sol_user');

        // Estado del entorno
        $environmentStat = Stat::make('Entorno SUNAT', $environment ?: 'No configurado')
            ->description($environment === 'produccion' ? 'Entorno de producción activo' : 'Entorno de pruebas activo')
            ->descriptionIcon('heroicon-o-cog')
            ->color($environment === 'produccion' ? 'success' : 'info');

        // Estado del certificado
        $certificateExists = $certificatePath && file_exists($certificatePath);
        $certificateStat = Stat::make('Certificado Digital', $certificateExists ? 'Cargado' : 'No cargado')
            ->description($certificateExists ? 'Certificado válido encontrado' : 'Debe cargar un certificado')
            ->descriptionIcon('heroicon-o-document')
            ->color($certificateExists ? 'success' : 'warning');

        // Estado del usuario SOL
        $userConfigured = !empty($solUser);
        $userStat = Stat::make('Usuario SOL', $userConfigured ? 'Configurado' : 'No configurado')
            ->description($userConfigured ? "Usuario: {$solUser}" : 'Debe configurar usuario SOL')
            ->descriptionIcon('heroicon-o-user')
            ->color($userConfigured ? 'success' : 'danger');

        return [
            $environmentStat,
            $certificateStat,
            $userStat,
        ];
    }
}
