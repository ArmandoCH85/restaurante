@extends('layouts.pos')

@section('content')
    @if(isset($tableId) && isset($orderId))
        <livewire:pos.point-of-sale :tableId="$tableId" :orderId="$orderId" :preserveCart="$preserveCart ?? false" />
    @elseif(isset($tableId))
        <livewire:pos.point-of-sale :tableId="$tableId" />
    @elseif(isset($orderId))
        <livewire:pos.point-of-sale :orderId="$orderId" :preserveCart="$preserveCart ?? false" />
    @elseif(isset($serviceType))
        <livewire:pos.point-of-sale :serviceType="$serviceType" />
    @else
        <livewire:pos.point-of-sale />
    @endif
@endsection
