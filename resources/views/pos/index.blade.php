@extends('layouts.pos')

@section('content')
    @if(isset($tableId))
        <livewire:pos.point-of-sale :tableId="$tableId" />
    @else
        <livewire:pos.point-of-sale />
    @endif
@endsection
