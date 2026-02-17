<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliveryGeocodingService
{
    public function geocodeDeliveryOrder(DeliveryOrder $deliveryOrder, bool $force = false): bool
    {
        $address = $this->resolveAddressForGeocoding($deliveryOrder);

        if ($address === '') {
            return false;
        }

        if (! $force && $deliveryOrder->delivery_latitude !== null && $deliveryOrder->delivery_longitude !== null) {
            return true;
        }

        $coordinates = $this->geocodeAddress($address);

        if (! $coordinates) {
            $deliveryOrder->forceFill([
                'delivery_latitude' => null,
                'delivery_longitude' => null,
            ])->saveQuietly();

            return false;
        }

        $deliveryOrder->forceFill([
            'delivery_latitude' => $coordinates['lat'],
            'delivery_longitude' => $coordinates['lng'],
        ])->saveQuietly();

        return true;
    }

    public function resolveAddressForGeocoding(DeliveryOrder $deliveryOrder): string
    {
        $recipientAddress = trim((string) $deliveryOrder->recipient_address);
        if ($recipientAddress !== '') {
            return $recipientAddress;
        }

        return trim((string) $deliveryOrder->delivery_address);
    }

    public function geocodeAddress(string $address): ?array
    {
        $normalizedAddress = $this->normalizeAddress($address);
        $cacheKey = 'geocode:nominatim:'.sha1($normalizedAddress);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached['not_found'] ?? false ? null : $cached;
        }

        $this->throttlePublicNominatim();

        try {
            $baseUrl = rtrim(config('services.nominatim.base_url', 'https://nominatim.openstreetmap.org'), '/');

            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => config('services.nominatim.user_agent', 'RestauranteApp/1.0 (admin@localhost)'),
                    'Referer' => config('services.nominatim.referer', config('app.url')),
                ])
                ->get($baseUrl.'/search', [
                    'q' => $normalizedAddress,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 0,
                ]);

            if (! $response->successful()) {
                Log::warning('Nominatim respondió error', [
                    'status' => $response->status(),
                    'address' => $normalizedAddress,
                ]);

                return null;
            }

            $result = $response->json();
            if (! is_array($result) || empty($result[0]['lat']) || empty($result[0]['lon'])) {
                Cache::put($cacheKey, ['not_found' => true], now()->addDays(3));

                return null;
            }

            $coordinates = [
                'lat' => round((float) $result[0]['lat'], 7),
                'lng' => round((float) $result[0]['lon'], 7),
            ];

            Cache::put($cacheKey, $coordinates, now()->addDays(30));

            return $coordinates;
        } catch (\Throwable $e) {
            Log::warning('Error geocodificando dirección con Nominatim', [
                'address' => $normalizedAddress,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizeAddress(string $address): string
    {
        $address = mb_strtolower(trim($address));
        $address = preg_replace('/\s+/', ' ', $address);

        return (string) $address;
    }

    private function throttlePublicNominatim(): void
    {
        $lock = Cache::lock('nominatim:public:throttle:lock', 5);

        $lock->block(10, function (): void {
            $lastRequest = (float) Cache::get('nominatim:public:last_request_ts', 0);
            $now = microtime(true);
            $elapsed = $now - $lastRequest;

            if ($elapsed < 1.0) {
                usleep((int) ((1.0 - $elapsed) * 1_000_000));
            }

            Cache::put('nominatim:public:last_request_ts', microtime(true), now()->addMinutes(10));
        });
    }
}
