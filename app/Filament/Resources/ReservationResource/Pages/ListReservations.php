<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(static::getResource()::getModel()::count()),
                
            'today' => Tab::make('Hoy')
                ->badge(static::getResource()::getModel()::whereDate('reservation_date', Carbon::today())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('reservation_date', Carbon::today())),
                
            'upcoming' => Tab::make('Próximas')
                ->badge(static::getResource()::getModel()::whereDate('reservation_date', '>', Carbon::today())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('reservation_date', '>', Carbon::today())),
                
            'pending' => Tab::make('Pendientes')
                ->badge(static::getResource()::getModel()::where('status', 'pending')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
                
            'confirmed' => Tab::make('Confirmadas')
                ->badge(static::getResource()::getModel()::where('status', 'confirmed')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')),
                
            'cancelled' => Tab::make('Canceladas')
                ->badge(static::getResource()::getModel()::where('status', 'cancelled')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
                
            'completed' => Tab::make('Completadas')
                ->badge(static::getResource()::getModel()::where('status', 'completed')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
        ];
    }
}
