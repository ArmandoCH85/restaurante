@php
    $summary = $getState() ?? [];
    $kpis = $summary['kpis'] ?? [];
    $conciliacion = $summary['conciliacion'] ?? [];
    $efectivo = $summary['efectivo'] ?? [];
    $otros = $summary['otros_metodos'] ?? [];
    $egresos = $summary['egresos'] ?? [];

    $difference = (float) ($kpis['diferencia'] ?? 0);
    $differenceStatus = $difference === 0.0 ? 'ok' : ($difference > 0 ? 'warning' : 'danger');

    $money = static fn ($value) => 'S/ '.number_format((float) $value, 2);
@endphp

<div class="cash-report" data-difference="{{ $differenceStatus }}">
    <div class="cash-report__hero">
        <div>
            <p class="cash-report__eyebrow">Resumen financiero</p>
            <h3 class="cash-report__title">Cierre de caja</h3>
        </div>
        <span class="cash-report__status">
            {{ $difference === 0.0 ? 'Sin diferencia' : ($difference > 0 ? 'Sobrante' : 'Faltante') }}
        </span>
    </div>

    <div class="cash-report__kpis">
        <article class="cash-report-card">
            <p class="cash-report-card__label">Ingresos</p>
            <p class="cash-report-card__value">{{ $money($kpis['total_ingresos'] ?? 0) }}</p>
        </article>
        <article class="cash-report-card">
            <p class="cash-report-card__label">Egresos</p>
            <p class="cash-report-card__value">{{ $money($kpis['total_egresos'] ?? 0) }}</p>
        </article>
        <article class="cash-report-card">
            <p class="cash-report-card__label">Ganancia real</p>
            <p class="cash-report-card__value">{{ $money($kpis['ganancia_real'] ?? 0) }}</p>
        </article>
        <article class="cash-report-card cash-report-card--difference">
            <p class="cash-report-card__label">Diferencia</p>
            <p class="cash-report-card__value">{{ $money($difference) }}</p>
        </article>
    </div>

    <div class="cash-report__grid">
        <section class="cash-report-panel">
            <h4 class="cash-report-panel__title">Conciliacion</h4>
            <dl class="cash-report-list">
                <div class="cash-report-list__row"><dt>Monto esperado</dt><dd>{{ $money($conciliacion['monto_esperado'] ?? 0) }}</dd></div>
                <div class="cash-report-list__row"><dt>Total manual (ventas)</dt><dd>{{ $money($conciliacion['total_manual_ventas'] ?? 0) }}</dd></div>
                <div class="cash-report-list__row"><dt>Monto inicial</dt><dd>{{ $money($conciliacion['monto_inicial'] ?? 0) }}</dd></div>
            </dl>
            <p class="cash-report-note">Formula: (Manual + Inicial) - Esperado</p>
        </section>

        <section class="cash-report-panel">
            <h4 class="cash-report-panel__title">Efectivo contado</h4>
            <p class="cash-report-panel__amount">{{ $money($efectivo['total_contado'] ?? 0) }}</p>
            <div class="cash-report-chips">
                @foreach (($efectivo['billetes'] ?? []) as $label => $qty)
                    <span class="cash-report-chip">S/{{ $label }} x {{ (int) $qty }}</span>
                @endforeach
                @foreach (($efectivo['monedas'] ?? []) as $label => $qty)
                    <span class="cash-report-chip">S/{{ $label }} x {{ (int) $qty }}</span>
                @endforeach
            </div>
        </section>
    </div>

    <div class="cash-report__grid">
        <section class="cash-report-panel">
            <h4 class="cash-report-panel__title">Otros metodos</h4>
            <ul class="cash-report-listing">
                @php $hasOther = false; @endphp
                @foreach ($otros as $name => $amount)
                    @if ((float) $amount > 0)
                        @php $hasOther = true; @endphp
                        <li><span>{{ ucfirst(str_replace('_', ' ', $name)) }}</span><strong>{{ $money($amount) }}</strong></li>
                    @endif
                @endforeach
                @if (! $hasOther)
                    <li><span>Sin registros</span><strong>-</strong></li>
                @endif
            </ul>
        </section>

        <section class="cash-report-panel">
            <h4 class="cash-report-panel__title">Egresos registrados</h4>
            <div class="cash-report-list__row"><span>Total egresos</span><strong>{{ $money($egresos['total'] ?? 0) }}</strong></div>
            <a class="cash-report-link" href="{{ $egresos['url'] ?? '/admin/egresos' }}" target="_blank" rel="noopener noreferrer">Ver detalle de egresos</a>
        </section>
    </div>
</div>
