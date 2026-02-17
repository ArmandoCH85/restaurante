<x-filament-panels::page>
    <div class="max-w-3xl mx-auto w-full">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-6 space-y-6">
            <form wire:submit="createSimpleDelivery" class="space-y-4">
                {{ $this->simpleForm }}

                <x-filament::section wire:ignore>
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Ubica el delivery en el mapa: busca por direccion o mueve el marcador manualmente.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <x-filament::input.wrapper class="md:col-span-2">
                                <x-filament::input id="manual-map-search" placeholder="Ej: Calle Mercedes Indacochea 115, Tacna, Peru" />
                            </x-filament::input.wrapper>

                            <div class="flex gap-2">
                                <x-filament::button type="button" size="sm" color="gray" id="btn-search-current-address">
                                    Buscar direccion
                                </x-filament::button>
                                <x-filament::button type="button" size="sm" color="warning" id="btn-clear-point">
                                    Limpiar
                                </x-filament::button>
                            </div>
                        </div>

                        <div id="simple-delivery-map" style="height: 360px; border-radius: 12px; overflow: hidden;"></div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <span>Lat: <strong id="manual-lat">-</strong></span>
                            <span class="ml-3">Lng: <strong id="manual-lng">-</strong></span>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Si el cliente da una referencia, ajusta el pin manualmente para mayor precision.
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Formato recomendado: <strong>Via + numero, distrito/ciudad, Peru</strong>. Ejemplo: <strong>Av. Bolognesi 235, Tacna, Peru</strong>.
                        </p>
                    </div>
                </x-filament::section>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <x-filament::button type="submit" icon="heroicon-o-check-circle" color="success">
                        Continuar al POS
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ url('/admin/ventas/delivery') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Volver a la vista avanzada</a>
        </div>
    </div>
</x-filament-panels::page>

@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
@endpush

@push('scripts')
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script>
        (function () {
            const STATE_KEY = '__simpleDeliveryMapState';
            const DEBUG_PREFIX = '[DeliverySimpleMap]';

            function getLivewireComponent() {
                const el = document.querySelector('[wire\\:id]');
                if (!el || !window.Livewire) {
                    return null;
                }

                return window.Livewire.find(el.getAttribute('wire:id'));
            }

            function debugLog(event, context = {}, sendToServer = false) {
                try {
                    console.log(`${DEBUG_PREFIX} ${event}`, context);
                } catch (e) {}

                if (!sendToServer) {
                    return;
                }

                const lw = getLivewireComponent();
                if (!lw || typeof lw.call !== 'function') {
                    return;
                }

                try {
                    lw.call('logMapDebug', event, context);
                } catch (e) {
                    console.warn(`${DEBUG_PREFIX} no se pudo enviar log al servidor`, e);
                }
            }

            function getTargetAddress() {
                const manualSearch = (document.getElementById('manual-map-search')?.value || '').trim();
                if (manualSearch !== '') {
                    return manualSearch;
                }

                const lw = getLivewireComponent();
                const stateRecipient = (
                    lw?.get?.('simple.recipient_address')
                    ?? lw?.$wire?.get?.('simple.recipient_address')
                    ?? ''
                ).toString().trim();
                const stateAddress = (
                    lw?.get?.('simple.address')
                    ?? lw?.$wire?.get?.('simple.address')
                    ?? ''
                ).toString().trim();

                const recipientEl = document.querySelector(
                    'textarea[name$="[recipient_address]"], textarea[name$=".recipient_address"], textarea[name="simple.recipient_address"], textarea[name="data.simple.recipient_address"], textarea[id$="recipient_address"]'
                );
                const addressEl = document.querySelector(
                    'textarea[name$="[address]"], textarea[name$=".address"], textarea[name="simple.address"], textarea[name="data.simple.address"], textarea[id$="address"]'
                );
                const domRecipient = (recipientEl?.value || '').trim();
                const domAddress = (addressEl?.value || '').trim();

                if (stateRecipient !== '') return stateRecipient;
                if (domRecipient !== '') return domRecipient;
                if (stateAddress !== '') return stateAddress;
                if (domAddress !== '') return domAddress;
                return '';
            }

            async function geocodeAddress(query) {
                const lw = getLivewireComponent();
                if (!lw || typeof lw.call !== 'function') {
                    debugLog('geocode.backend.livewire_missing', {}, true);
                    return null;
                }

                debugLog('geocode.request.started', { query }, true);

                let response;
                try {
                    response = await lw.call('geocodeAddressForMap', query);
                } catch (error) {
                    debugLog('geocode.backend.call_error', {
                        query,
                        error: error?.message ?? 'Error desconocido llamando Livewire',
                    }, true);
                    return null;
                }

                debugLog('geocode.backend.response', { query, response }, true);

                if (!response?.ok) {
                    return null;
                }

                const lat = Number(response.lat);
                const lng = Number(response.lng);
                if (Number.isNaN(lat) || Number.isNaN(lng)) {
                    debugLog('geocode.backend.invalid_coordinates', { response }, true);
                    return null;
                }

                return { lat, lng };
            }

            function setCoords(lat, lng) {
                const lw = getLivewireComponent();
                if (lw) {
                    lw.set('simple.delivery_latitude', Number(lat).toFixed(7), false);
                    lw.set('simple.delivery_longitude', Number(lng).toFixed(7), false);
                }

                const latEl = document.getElementById('manual-lat');
                const lngEl = document.getElementById('manual-lng');
                if (latEl) latEl.textContent = Number(lat).toFixed(7);
                if (lngEl) lngEl.textContent = Number(lng).toFixed(7);

                debugLog('coords.updated', {
                    lat: Number(lat).toFixed(7),
                    lng: Number(lng).toFixed(7),
                }, false);
            }

            function clearCoords() {
                const lw = getLivewireComponent();
                if (lw) {
                    lw.set('simple.delivery_latitude', null, false);
                    lw.set('simple.delivery_longitude', null, false);
                }

                const latEl = document.getElementById('manual-lat');
                const lngEl = document.getElementById('manual-lng');
                if (latEl) latEl.textContent = '-';
                if (lngEl) lngEl.textContent = '-';
                debugLog('coords.cleared', {}, false);
            }

            function initMap() {
                if (window[STATE_KEY]?.map) {
                    const existingContainer = window[STATE_KEY].map.getContainer?.();
                    if (existingContainer && document.body.contains(existingContainer)) {
                        return;
                    }

                    try {
                        window[STATE_KEY].map.remove();
                    } catch (e) {}
                    window[STATE_KEY] = null;
                }

                const container = document.getElementById('simple-delivery-map');
                if (!container || typeof L === 'undefined') {
                    debugLog('map.init.skipped', {
                        hasContainer: Boolean(container),
                        leafletLoaded: typeof L !== 'undefined',
                    }, true);
                    return;
                }

                const map = L.map(container).setView([-12.0464, -77.0428], 13);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
                }).addTo(map);
                debugLog('map.init.ok', {}, true);

                let marker = null;
                function putMarker(lat, lng) {
                    if (!marker) {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', function () {
                            const p = marker.getLatLng();
                            setCoords(p.lat, p.lng);
                        });
                    } else {
                        marker.setLatLng([lat, lng]);
                    }

                    map.panTo([lat, lng]);
                    setCoords(lat, lng);
                }

                map.on('click', function (e) {
                    putMarker(e.latlng.lat, e.latlng.lng);
                });

                const btnSearchCurrent = document.getElementById('btn-search-current-address');
                if (!btnSearchCurrent) {
                    debugLog('ui.button.search_not_found', {}, true);
                }
                btnSearchCurrent?.addEventListener('click', async function () {
                    const query = getTargetAddress();
                    debugLog('ui.button.search_clicked', {
                        query,
                        hasLivewire: Boolean(getLivewireComponent()),
                    }, true);
                    if (!query) {
                        debugLog('ui.button.search_empty_query', {}, true);
                        return;
                    }
                    const searchInput = document.getElementById('manual-map-search');
                    if (searchInput && !searchInput.value) {
                        searchInput.value = query;
                    }
                    const result = await geocodeAddress(query);
                    if (!result) {
                        debugLog('ui.button.search_no_result', { query }, true);
                        alert('No se encontro la direccion en el mapa. Prueba agregando distrito o provincia, o mueve el pin manualmente.');
                        return;
                    }
                    putMarker(result.lat, result.lng);
                });

                const btnClear = document.getElementById('btn-clear-point');
                if (!btnClear) {
                    debugLog('ui.button.clear_not_found', {}, true);
                }
                btnClear?.addEventListener('click', function () {
                    if (marker) {
                        map.removeLayer(marker);
                        marker = null;
                    }
                    clearCoords();
                });

                const searchInput = document.getElementById('manual-map-search');
                if (!searchInput) {
                    debugLog('ui.input.search_not_found', {}, true);
                }
                searchInput?.addEventListener('keydown', async function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }
                    event.preventDefault();
                    const query = (searchInput.value || '').trim();
                    debugLog('ui.input.search_enter', { query }, true);
                    if (!query) {
                        debugLog('ui.input.search_enter_empty_query', {}, true);
                        return;
                    }
                    const result = await geocodeAddress(query);
                    if (!result) {
                        debugLog('ui.input.search_enter_no_result', { query }, true);
                        alert('No se encontro la direccion en el mapa. Prueba con mas detalle o ajusta el pin manualmente.');
                        return;
                    }
                    putMarker(result.lat, result.lng);
                });

                window[STATE_KEY] = { map };
            }

            document.addEventListener('DOMContentLoaded', initMap);
            document.addEventListener('livewire:navigated', initMap);
        })();
    </script>
@endpush
