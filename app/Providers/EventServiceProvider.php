<?php

namespace App\Providers;

use App\Events\DeliveryStatusChanged;
use App\Events\PaymentRegistered;
use App\Events\PaymentVoided;
use App\Events\QuotationDetailCreated;
use App\Events\QuotationDetailUpdated;
use App\Events\QuotationDetailDeleted;
use App\Listeners\SendDeliveryStatusNotification;
use App\Listeners\UpdateCashRegisterTotals;
use App\Listeners\UpdateCashRegisterForVoidedPayment;
use App\Listeners\RecalculateQuotationTotals;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        DeliveryStatusChanged::class => [
            SendDeliveryStatusNotification::class,
        ],
        PaymentRegistered::class => [
            UpdateCashRegisterTotals::class,
        ],
        PaymentVoided::class => [
            UpdateCashRegisterForVoidedPayment::class,
        ],
        QuotationDetailCreated::class => [
            [RecalculateQuotationTotals::class, 'handleCreated'],
        ],
        QuotationDetailUpdated::class => [
            [RecalculateQuotationTotals::class, 'handleUpdated'],
        ],
        QuotationDetailDeleted::class => [
            [RecalculateQuotationTotals::class, 'handleDeleted'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
