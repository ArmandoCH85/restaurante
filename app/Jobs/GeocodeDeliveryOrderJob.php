<?php

namespace App\Jobs;

use App\Models\DeliveryOrder;
use App\Services\DeliveryGeocodingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeocodeDeliveryOrderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $deliveryOrderId,
        public bool $force = false
    ) {}

    public function handle(DeliveryGeocodingService $geocodingService): void
    {
        $deliveryOrder = DeliveryOrder::query()->find($this->deliveryOrderId);

        if (! $deliveryOrder) {
            return;
        }

        $geocodingService->geocodeDeliveryOrder($deliveryOrder, $this->force);
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning('Fallo job de geocodificacion de delivery', [
            'delivery_order_id' => $this->deliveryOrderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
