<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;

class TestNumberToWords extends Command
{
    protected $signature = 'test:number-to-words {number?}';
    protected $description = 'Probar la conversión de números a letras para XML SUNAT';

    public function handle()
    {
        $number = $this->argument('number');

        if (!$number) {
            $this->info('🧮 Probando conversión de números a letras para XML SUNAT');
            $this->line('');

            // Números de prueba típicos de restaurante
            $testNumbers = [
                43.66,   // Ejemplo del usuario
                25.50,   // Plato típico
                100.00,  // Cuenta redonda
                15.75,   // Bebida
                250.30,  // Cuenta familiar
                1.00,    // Propina
                99.99,   // Cuenta casi 100
                500.00   // Evento grande
            ];

            foreach ($testNumbers as $testNumber) {
                $this->testNumber($testNumber);
            }
        } else {
            $this->testNumber((float)$number);
        }

        return 0;
    }

    private function testNumber($number)
    {
        try {
            $sunatService = new SunatService();

            // Usar reflexión para acceder al método privado
            $reflection = new \ReflectionClass($sunatService);
            $method = $reflection->getMethod('convertirNumeroALetras');
            $method->setAccessible(true);

            $result = $method->invoke($sunatService, $number);

            $this->line("💰 <fg=cyan>S/ " . number_format($number, 2) . "</>");
            $this->line("📝 <fg=green>" . $result . "</>");
            $this->line("🔖 XML Note: <fg=yellow><cbc:Note languageLocaleID=\"1000\"><![CDATA[SON " . $result . " SOLES]]></cbc:Note></>");
            $this->line("🏷️  XML Legend: <fg=blue><cbc:ID>1000</cbc:ID><cbc:Note><![CDATA[SON " . $result . " SOLES]]></cbc:Note></>");
            $this->line('');

        } catch (\Exception $e) {
            $this->error("❌ Error al convertir {$number}: " . $e->getMessage());
        }
    }
}
