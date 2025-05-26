<?php

namespace App\Filament\Resources\ElectronicBillingConfigResource\Pages;

use App\Filament\Resources\ElectronicBillingConfigResource;
use App\Models\AppSetting;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Filament\Forms;

class ManageElectronicBillingConfig extends ManageRecords
{
    protected static string $resource = ElectronicBillingConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload_certificate')
                ->label('Subir Certificado')
                ->color('primary')
                ->icon('heroicon-o-document-plus')
                ->form([
                    Forms\Components\FileUpload::make('certificate')
                        ->label('Certificado Digital')
                        ->helperText('Sube tu certificado digital (.p12, .pfx, .pem)')
                        ->maxSize(5120)
                        ->disk('certificates')
                        ->directory(function () {
                            $environment = AppSetting::getSetting('FacturacionElectronica', 'environment') ?: 'beta';
                            return $environment;
                        })
                        ->visibility('private')
                        ->required(),
                ])
                ->action(function (array $data) {
                    if (isset($data['certificate'])) {
                        $certificatePath = $data['certificate'];
                        $fileName = basename($certificatePath);
                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        // Validar extensión
                        if (!in_array($extension, ['p12', 'pfx', 'pem'])) {
                            Notification::make()
                                ->title('Error en el archivo')
                                ->body('El archivo debe tener extensión .p12, .pfx o .pem')
                                ->danger()
                                ->send();
                            return;
                        }

                        $environment = AppSetting::getSetting('FacturacionElectronica', 'environment') ?: 'beta';
                        $fullPath = storage_path("app/private/sunat/certificates/{$environment}/" . $fileName);

                        // Crear directorio si no existe
                        $directory = dirname($fullPath);
                        if (!file_exists($directory)) {
                            mkdir($directory, 0755, true);
                        }

                        AppSetting::updateOrCreate(
                            ['tab' => 'FacturacionElectronica', 'key' => 'certificate_path'],
                            ['value' => $fullPath]
                        );

                        Notification::make()
                            ->title('Certificado cargado exitosamente')
                            ->body("El certificado {$fileName} ha sido guardado en el entorno: {$environment}")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    }
                }),

            Actions\Action::make('test_connection')
                ->label('Probar Conexión')
                ->color('info')
                ->icon('heroicon-o-wifi')
                ->action(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment');
                    $user = AppSetting::getSetting('FacturacionElectronica', 'sol_user');
                    $certificatePath = AppSetting::getSetting('FacturacionElectronica', 'certificate_path');

                    $errors = [];

                    if (!$user) {
                        $errors[] = 'Usuario SOL no configurado';
                    }

                    if (!$certificatePath || !file_exists($certificatePath)) {
                        $errors[] = 'Certificado digital no encontrado';
                    }

                    if (empty($errors)) {
                        Notification::make()
                            ->title('Configuración válida')
                            ->body("Entorno: {$env} | Usuario: {$user}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Errores en la configuración')
                            ->body(implode(', ', $errors))
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('switch_environment')
                ->label(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment');
                    return $env === 'production' ? 'Cambiar a Beta' : 'Cambiar a Producción';
                })
                ->color(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment');
                    return $env === 'production' ? 'warning' : 'success';
                })
                ->icon(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment');
                    return $env === 'production' ? 'heroicon-o-arrow-down' : 'heroicon-o-arrow-up';
                })
                ->requiresConfirmation()
                ->modalHeading('Cambiar Entorno SUNAT')
                ->modalDescription(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment');
                    $newEnv = $env === 'production' ? 'beta' : 'production';
                    return "¿Está seguro de cambiar del entorno '{$env}' al entorno '{$newEnv}'?";
                })
                ->action(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment');
                    $newEnv = $env === 'production' ? 'beta' : 'production';

                    AppSetting::where('tab', 'FacturacionElectronica')
                        ->where('key', 'environment')
                        ->update(['value' => $newEnv]);

                    Notification::make()
                        ->title('Entorno cambiado')
                        ->body("Cambiado de '{$env}' a '{$newEnv}'")
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('view_files')
                ->label('Ver Archivos XML')
                ->color('info')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $env = AppSetting::getSetting('FacturacionElectronica', 'environment') ?: 'beta';
                    $xmlDir = storage_path("app/private/sunat/xml/{$env}/signed");

                    $files = [];
                    if (File::exists($xmlDir)) {
                        $fileList = File::files($xmlDir);
                        foreach ($fileList as $file) {
                            $files[] = [
                                'name' => $file->getFilename(),
                                'size' => round($file->getSize() / 1024, 2) . ' KB',
                                'date' => date('d/m/Y H:i:s', $file->getMTime()),
                                'path' => $file->getPathname()
                            ];
                        }
                    }

                    $fileList = '';
                    if (empty($files)) {
                        $fileList = 'No hay archivos XML generados aún.';
                    } else {
                        foreach ($files as $file) {
                            $fileList .= "📄 {$file['name']}\n";
                            $fileList .= "   📊 Tamaño: {$file['size']}\n";
                            $fileList .= "   📅 Fecha: {$file['date']}\n\n";
                        }
                    }

                    Notification::make()
                        ->title('Archivos XML Generados')
                        ->body($fileList)
                        ->info()
                        ->duration(10000)
                        ->send();
                }),

            Actions\Action::make('clear_certificates')
                ->label('Limpiar Certificados')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Certificados')
                ->modalDescription('¿Está seguro de eliminar todos los certificados cargados? Esta acción no se puede deshacer.')
                ->action(function () {
                    $directories = [
                        storage_path('app/private/sunat/certificates/beta'),
                        storage_path('app/private/sunat/certificates/production'),
                    ];

                    $deletedFiles = 0;
                    foreach ($directories as $directory) {
                        if (File::exists($directory)) {
                            $files = File::files($directory);
                            foreach ($files as $file) {
                                if (in_array($file->getExtension(), ['p12', 'pfx', 'pem'])) {
                                    File::delete($file->getPathname());
                                    $deletedFiles++;
                                }
                            }
                        }
                    }

                    // Limpiar la ruta del certificado en la configuración
                    AppSetting::where('tab', 'FacturacionElectronica')
                        ->where('key', 'certificate_path')
                        ->update(['value' => '']);

                    Notification::make()
                        ->title('Certificados eliminados')
                        ->body("Se eliminaron {$deletedFiles} certificados")
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SunatConfigurationOverview::class,
        ];
    }
}
