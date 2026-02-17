<?php

use App\Models\DeliveryOrder;
use App\Services\DeliveryGeocodingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Cache::flush();
});

it('prioriza recipient_address para geocodificar', function (): void {
    $service = app(DeliveryGeocodingService::class);

    $deliveryOrder = new DeliveryOrder([
        'delivery_address' => 'Av. Principal 123, Lima',
        'recipient_address' => 'Jr. Alterno 456, Lima',
    ]);

    expect($service->resolveAddressForGeocoding($deliveryOrder))
        ->toBe('Jr. Alterno 456, Lima');
});

it('usa delivery_address cuando recipient_address esta vacio', function (): void {
    $service = app(DeliveryGeocodingService::class);

    $deliveryOrder = new DeliveryOrder([
        'delivery_address' => 'Av. Principal 123, Lima',
        'recipient_address' => '   ',
    ]);

    expect($service->resolveAddressForGeocoding($deliveryOrder))
        ->toBe('Av. Principal 123, Lima');
});

it('consulta nominatim con headers configurados y cachea coordenadas', function (): void {
    config()->set('services.nominatim.base_url', 'https://nominatim.openstreetmap.org');
    config()->set('services.nominatim.user_agent', 'RestauranteDeliveryMap/qa');
    config()->set('services.nominatim.referer', 'http://restaurante.test');

    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([
            [
                'lat' => '-12.0456789',
                'lon' => '-77.0312345',
            ],
        ], 200),
    ]);

    $service = app(DeliveryGeocodingService::class);
    $first = $service->geocodeAddress('Av. Larco 123, Miraflores');
    $second = $service->geocodeAddress('Av. Larco 123, Miraflores');

    expect($first)->toBe([
        'lat' => -12.0456789,
        'lng' => -77.0312345,
    ])->and($second)->toBe($first);

    Http::assertSentCount(1);
    Http::assertSent(function ($request): bool {
        return $request->hasHeader('User-Agent', 'RestauranteDeliveryMap/qa')
            && $request->hasHeader('Referer', 'http://restaurante.test');
    });
});
