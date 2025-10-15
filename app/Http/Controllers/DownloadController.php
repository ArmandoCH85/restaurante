<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadController extends Controller
{
    /**
     * Descargar archivo temporal
     */
    public function downloadTempFile(Request $request, string $path, string $name)
    {
        // Verificar que el archivo existe en el directorio temporal
        $tempPath = storage_path('app/temp/' . $path);
        
        if (!file_exists($tempPath)) {
            abort(404, 'Archivo no encontrado');
        }
        
        // Crear respuesta de archivo
        $response = new BinaryFileResponse($tempPath);
        
        // Configurar headers para descarga
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name,
            iconv('UTF-8', 'ASCII//TRANSLIT', $name)
        );
        
        // Establecer headers adicionales
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $response->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        
        // Registrar en el log
        \App\Filament\Pages\ReportViewerPageLog::writeRaw("📥 Archivo temporal descargado: " . $path . " como " . $name . "\n");
        
        // Eliminar el archivo después de la descarga
        register_shutdown_function(function() use ($tempPath) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        });
        
        return $response;
    }
}