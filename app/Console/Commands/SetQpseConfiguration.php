<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;

class SetQpseConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:set-qpse {--enable : Enable QPSE mode} {--disable : Disable QPSE mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure QPSE mode for SUNAT integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('enable')) {
            $this->enableQpse();
        } elseif ($this->option('disable')) {
            $this->disableQpse();
        } else {
            $this->showCurrentStatus();
            $this->askForConfiguration();
        }
    }

    private function enableQpse()
    {
        AppSetting::setSetting('FacturacionElectronica', 'use_qpse', 'true');
        $this->info('✅ QPSE mode has been ENABLED');
        $this->info('📄 Electronic invoicing will use QPSE instead of direct SUNAT certificates');
        $this->warn('⚠️  Make sure QPSE is properly configured in your system');
    }

    private function disableQpse()
    {
        AppSetting::setSetting('FacturacionElectronica', 'use_qpse', 'false');
        $this->info('✅ QPSE mode has been DISABLED');
        $this->info('📄 Electronic invoicing will use direct SUNAT certificates');
        $this->warn('⚠️  Make sure SUNAT certificates are properly configured');
    }

    private function showCurrentStatus()
    {
        $useQpse = AppSetting::getSetting('FacturacionElectronica', 'use_qpse') === 'true';
        $environment = AppSetting::getSetting('FacturacionElectronica', 'environment') ?: 'beta';
        
        $this->info('📊 Current SUNAT Configuration:');
        $this->table(['Setting', 'Value'], [
            ['QPSE Mode', $useQpse ? '✅ Enabled' : '❌ Disabled'],
            ['Environment', $environment],
            ['Integration Method', $useQpse ? 'QPSE' : 'Direct SUNAT']
        ]);
    }

    private function askForConfiguration()
    {
        $choice = $this->choice(
            'What would you like to do?',
            ['Enable QPSE', 'Disable QPSE', 'Exit'],
            2
        );

        switch ($choice) {
            case 'Enable QPSE':
                $this->enableQpse();
                break;
            case 'Disable QPSE':
                $this->disableQpse();
                break;
            case 'Exit':
                $this->info('No changes made.');
                break;
        }
    }
}
