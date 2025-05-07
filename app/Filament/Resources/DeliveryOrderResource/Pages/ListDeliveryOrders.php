<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryOrders extends ListRecords
{
    protected static string $resource = DeliveryOrderResource::class;



    protected function getHeaderActions(): array
    {
        // Obtener el usuario actual
        $user = \Illuminate\Support\Facades\Auth::user();

        // Si el usuario tiene rol Delivery, no mostrar el botón de crear
        if ($user && $user->roles->where('name', 'Delivery')->count() > 0) {
            return [];
        }

        return [
            Actions\CreateAction::make(),
        ];
    }
}
