<?php

namespace App\Providers;

use App\Models\Quotation;
use App\Policies\QuotationPolicy;
use App\Models\CreditNote;
use App\Policies\CreditNotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Quotation::class => QuotationPolicy::class,
        CreditNote::class => CreditNotePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registrar políticas
        $this->registerPolicies();

        // Definir gates personalizados para acciones específicas de cotizaciones
        Gate::define('approve-quotation', [QuotationPolicy::class, 'approve']);
        Gate::define('reject-quotation', [QuotationPolicy::class, 'reject']);
        Gate::define('send-quotation', [QuotationPolicy::class, 'send']);
        Gate::define('convert-quotation-to-order', [QuotationPolicy::class, 'convertToOrder']);
    }
}
