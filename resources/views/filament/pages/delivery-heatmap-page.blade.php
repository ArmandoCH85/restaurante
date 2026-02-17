<x-filament-panels::page>
    @php
        $kpis = $this->getKpis();
        $payload = $this->getHeatmapPayload();
    @endphp

    <div class="space-y-4">
        <x-filament::section>
            <p class="text-sm text-gray-600">
                Mapa de calor operativo de pedidos delivery. Solo se incluyen pedidos con coordenadas guardadas.
            </p>
        </x-filament::section>

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-filament::section>
                <div class="text-sm text-gray-500">Total deliveries en rango</div>
                <div class="text-2xl font-bold">{{ number_format($kpis['total_deliveries']) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Con coordenadas</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($kpis['with_coordinates']) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Sin geolocalizar</div>
                <div class="text-2xl font-bold text-amber-600">{{ number_format($kpis['without_geolocation']) }}</div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <div id="delivery-heatmap" style="height: 560px; width: 100%; border-radius: 12px; overflow: hidden;"></div>
            <p class="mt-2 text-xs text-gray-500">
                © OpenStreetMap contributors -
                <a href="https://www.openstreetmap.org/copyright" target="_blank" class="underline">ODbL</a>
            </p>
        </x-filament::section>
    </div>

    @once
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>
        <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

        <script>
            (function () {
                if (!window.deliveryHeatmapState) {
                    window.deliveryHeatmapState = {
                        map: null,
                        layer: null,
                        intervalId: null,
                    };
                }

                function initMap() {
                    const state = window.deliveryHeatmapState;
                    const container = document.getElementById('delivery-heatmap');
                    if (!container || state.map) {
                        return;
                    }

                    state.map = L.map('delivery-heatmap').setView([-12.0464, -77.0428], 12);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a> (ODbL)',
                    }).addTo(state.map);

                    state.layer = L.heatLayer([], {
                        radius: 24,
                        blur: 18,
                        maxZoom: 17,
                    }).addTo(state.map);
                }

                function updateHeatmap(payload) {
                    initMap();
                    const state = window.deliveryHeatmapState;
                    if (!state.map || !state.layer) {
                        return;
                    }

                    const points = payload?.points || [];
                    state.layer.setLatLngs(points);

                    if (points.length > 0) {
                        const bounds = L.latLngBounds(points.map((point) => [point[0], point[1]]));
                        state.map.fitBounds(bounds.pad(0.15));
                    }
                }

                window.addEventListener('delivery-heatmap:update', function (event) {
                    updateHeatmap(event.detail || {});
                });
            })();
        </script>
    @endonce

    <script>
        window.dispatchEvent(new CustomEvent('delivery-heatmap:update', {
            detail: @json($payload),
        }));
    </script>

    @if($this->autoRefresh)
        <script>
            (function () {
                const state = window.deliveryHeatmapState;
                if (state.intervalId) {
                    clearInterval(state.intervalId);
                }

                state.intervalId = setInterval(function () {
                    if (window.Livewire) {
                        window.Livewire.find('{{ $this->getId() }}')?.call('refreshHeatmap');
                    }
                }, {{ (int) $this->refreshInterval * 1000 }});
            })();
        </script>
    @else
        <script>
            if (window.deliveryHeatmapState?.intervalId) {
                clearInterval(window.deliveryHeatmapState.intervalId);
                window.deliveryHeatmapState.intervalId = null;
            }
        </script>
    @endif
</x-filament-panels::page>
