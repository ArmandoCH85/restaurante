<?php

namespace App\Helpers;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfHelper
{
    /** Sanitiza datos recursivamente asegurando UTF-8 válido */
    public static function sanitizeData(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = str_replace(["\r\n"], "\n", $value);
            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value);
            if (!mb_check_encoding($value, 'UTF-8')) {
                $converted = @iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $value);
                if ($converted !== false) {
                    $value = $converted;
                } else {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, ASCII');
                }
            }
            $value = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            return $value;
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::sanitizeData($v);
            }
            return $value;
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $k => $v) {
                $value->$k = self::sanitizeData($v);
            }
            return $value;
        }
        return $value;
    }

    /** Genera un PDF de ticket 80mm sanitizando datos */
    public static function makeTicketPdf(string $view, array $data, int $itemsCount, array $paper = null)
    {
        $data = self::sanitizeData($data);
        // Renderizar vista primero
        $html = view($view, $data)->render();
        $originalLen = strlen($html);
        $htmlSanitized = self::sanitizeHtml($html);
        if ($htmlSanitized !== $html) {
            \Log::warning('PDF HTML sanitizado (posible bytes inválidos)', [
                'original_length' => $originalLen,
                'sanitized_length' => strlen($htmlSanitized),
            ]);
        }
        if (!$paper) {
            $basePt = 500; $perItemPt = 18; $heightPt = min(2600, $basePt + ($itemsCount * $perItemPt));
            $paper = [0,0,226.77,$heightPt];
        }
        return Pdf::loadHtml($htmlSanitized)->setPaper($paper, 'portrait');
    }

    /** Sanitiza HTML completo */
    public static function sanitizeHtml(string $html): string
    {
        // Normalizar a UTF-8 si viene en otra codificación común
        if (!mb_check_encoding($html, 'UTF-8')) {
            $converted = @iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $html);
            if ($converted !== false) {
                $html = $converted;
            } else {
                $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8, ISO-8859-1, ASCII');
            }
        }
        // Eliminar caracteres de control no permitidos
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $html);
        // Remover bytes sueltos inválidos de secuencias UTF-8
        if (!mb_check_encoding($html, 'UTF-8')) {
            $html = @iconv('UTF-8', 'UTF-8//IGNORE', $html);
        }
        return $html;
    }
}
