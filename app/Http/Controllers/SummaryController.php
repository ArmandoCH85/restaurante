<?php

namespace App\Http\Controllers;

use App\Models\Summary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SummaryController extends Controller
{
    /**
     * Descargar XML del resumen
     */
    public function downloadXml(Summary $summary)
    {
        if (!$summary->xml_path) {
            // Intentar con nombre por defecto basado en correlativo
            $correlativo = $summary->correlativo;
            $candidate = storage_path('app/sunat/summaries/xml/RC-' . $correlativo . '.xml');
            $altCandidate = storage_path('app/private/sunat/summaries/xml/RC-' . $correlativo . '.xml');
            
            if (File::exists($candidate)) {
                $path = $candidate;
            } elseif (File::exists($altCandidate)) {
                $path = $altCandidate;
            } else {
                abort(404, 'Archivo XML no encontrado');
            }
        } else {
            $path = $summary->xml_path;
            
            // Si es una ruta relativa, convertir a absoluta
            if (!File::exists($path)) {
                $normalized = ltrim(str_replace(['\\'], ['/'], $path), '/');
                $candidate = storage_path('app/' . $normalized);
                
                if (File::exists($candidate)) {
                    $path = $candidate;
                } else {
                    // Probar también bajo storage/app/private
                    $altCandidate = storage_path('app/private/' . $normalized);
                    if (File::exists($altCandidate)) {
                        $path = $altCandidate;
                    } else {
                        // Fallback final por correlativo
                        $correlativo = $summary->correlativo;
                        $default1 = storage_path('app/sunat/summaries/xml/RC-' . $correlativo . '.xml');
                        $default2 = storage_path('app/private/sunat/summaries/xml/RC-' . $correlativo . '.xml');
                        
                        if (File::exists($default1)) {
                            $path = $default1;
                        } elseif (File::exists($default2)) {
                            $path = $default2;
                        }
                    }
                }
            }

            if (!File::exists($path)) {
                abort(404, 'Archivo XML no encontrado');
            }
        }

        $filename = 'RC-' . $summary->correlativo . '.xml';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Descargar CDR del resumen
     */
    public function downloadCdr(Summary $summary)
    {
        if (!$summary->cdr_path) {
            // Intentar con nombre por defecto basado en correlativo
            $correlativo = $summary->correlativo;
            $candidate = storage_path('app/sunat/summaries/cdr/RC-' . $correlativo . '.zip');
            $altCandidate = storage_path('app/private/sunat/summaries/cdr/RC-' . $correlativo . '.zip');
            
            if (File::exists($candidate)) {
                $path = $candidate;
            } elseif (File::exists($altCandidate)) {
                $path = $altCandidate;
            } else {
                abort(404, 'Archivo CDR no encontrado');
            }
        } else {
            $path = $summary->cdr_path;
            
            // Si es una ruta relativa, convertir a absoluta
            if (!File::exists($path)) {
                $normalized = ltrim(str_replace(['\\'], ['/'], $path), '/');
                $candidate = storage_path('app/' . $normalized);
                
                if (File::exists($candidate)) {
                    $path = $candidate;
                } else {
                    // Probar también bajo storage/app/private
                    $altCandidate = storage_path('app/private/' . $normalized);
                    if (File::exists($altCandidate)) {
                        $path = $altCandidate;
                    } else {
                        // Fallback final por correlativo
                        $correlativo = $summary->correlativo;
                        $default1 = storage_path('app/sunat/summaries/cdr/RC-' . $correlativo . '.zip');
                        $default2 = storage_path('app/private/sunat/summaries/cdr/RC-' . $correlativo . '.zip');
                        
                        if (File::exists($default1)) {
                            $path = $default1;
                        } elseif (File::exists($default2)) {
                            $path = $default2;
                        }
                    }
                }
            }

            if (!File::exists($path)) {
                abort(404, 'Archivo CDR no encontrado');
            }
        }

        $filename = 'CDR-RC-' . $summary->correlativo . '.zip';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}