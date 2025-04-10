<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Resources\Pages\Page;
use App\Models\Table;
use App\Models\ProductCategory;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class TableMap extends Page
{
    protected static string $resource = TableResource::class;

    protected static string $view = 'filament.resources.table-resource.pages.table-map';

    public Collection $tables;
    public Collection $categories;
    public Collection $products;

    public ?Table $selectedTable = null;
    public ?string $selectedTableId = null;
    public ?string $selectedCategoryId = null;
    public ?string $productSearchQuery = null;

    public ?string $statusFilter = null;
    public ?string $locationFilter = null;

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'locationFilter' => ['except' => ''],
        'selectedTableId' => ['except' => ''],
        'showProductSelection' => ['except' => false],
    ];

    public bool $isModalOpen = false;
    public bool $showProductSelection = false;
    public bool $showTableDetails = false;

    public function mount(): void
    {
        $this->loadTables();
        $this->loadCategories();

        // Si hay un selectedTableId en la URL, cargar la mesa
        if ($this->selectedTableId) {
            $this->selectTable($this->selectedTableId);
        }
    }

    public function loadTables(): void
    {
        $query = Table::query();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->locationFilter) {
            $query->where('location', $this->locationFilter);
        }

        $this->tables = $query->get();
    }

    public function loadCategories(): void
    {
        // Cargar categorías ordenadas por display_order
        $this->categories = ProductCategory::where('visible_in_menu', true)
            ->orderBy('display_order')
            ->get();

        // Si hay categorías, seleccionar la primera por defecto
        if ($this->categories->isNotEmpty() && !$this->selectedCategoryId) {
            $this->selectedCategoryId = $this->categories->first()->id;
            $this->loadProductsByCategory($this->selectedCategoryId);
        }
    }

    public function loadProductsByCategory(string $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;

        $query = Product::where('category_id', $categoryId)
            ->where('active', true)
            ->where('available', true)
            ->where('product_type', '!=', 'ingredient');

        if ($this->productSearchQuery) {
            $query->where('name', 'like', "%{$this->productSearchQuery}%");
        }

        $this->products = $query->orderBy('name')->get();
    }

    public function searchProducts(): void
    {
        $this->loadProductsByCategory($this->selectedCategoryId);
    }

    public function resetProductSearch(): void
    {
        $this->productSearchQuery = null;
        $this->loadProductsByCategory($this->selectedCategoryId);
    }

    public function selectTable(string $tableId): void
    {
        $this->selectedTableId = $tableId;
        $this->selectedTable = Table::find($tableId);
        $this->showTableDetails = true;
    }

    public function unselectTable(): void
    {
        $this->selectedTableId = null;
        $this->selectedTable = null;
        $this->showTableDetails = false;
        $this->showProductSelection = false;
    }

    public function showProducts(): void
    {
        if (!$this->selectedTable) {
            Notification::make()
                ->title('No se ha seleccionado ninguna mesa')
                ->danger()
                ->send();
            return;
        }

        // Si la mesa no está disponible, mostrar advertencia
        if (!$this->selectedTable->isAvailable()) {
            Notification::make()
                ->title('No se puede crear un pedido en esta mesa')
                ->body('La mesa no está disponible en este momento.')
                ->warning()
                ->send();
            return;
        }

        $this->showProductSelection = true;
    }

    public function hideProducts(): void
    {
        $this->showProductSelection = false;
    }

    public function createOrder(): void
    {
        if (!$this->selectedTable) {
            Notification::make()
                ->title('No se ha seleccionado ninguna mesa')
                ->danger()
                ->send();
            return;
        }

        // Si la mesa no está disponible, mostrar advertencia
        if (!$this->selectedTable->isAvailable()) {
            Notification::make()
                ->title('No se puede crear un pedido en esta mesa')
                ->body('La mesa no está disponible en este momento.')
                ->warning()
                ->send();
            return;
        }

        // Redirigir a la creación de pedido con la mesa preseleccionada
        // Esto asume que existe una ruta para crear pedidos
        // Si no existe, puedes adaptarlo según tu aplicación
        $this->redirect(route('filament.admin.resources.tables.edit', ['record' => $this->selectedTable]));
    }

    public function updateTableStatus(string $tableId, string $status): void
    {
        $table = Table::find($tableId);

        if (!$table) {
            Notification::make()
                ->title('Mesa no encontrada')
                ->danger()
                ->send();
            return;
        }

        $table->update(['status' => $status]);

        $this->loadTables();

        if ($this->selectedTable && $this->selectedTable->id == $tableId) {
            // Actualizar la mesa seleccionada con los datos actualizados
            $this->selectedTable = $table->fresh();
        }

        Notification::make()
            ->title('Estado de mesa actualizado')
            ->success()
            ->send();
    }

    public function applyFilter(): void
    {
        $this->loadTables();
    }

    public function resetFilters(): void
    {
        $this->statusFilter = null;
        $this->locationFilter = null;

        $this->loadTables();
    }

    public function getStatusOptions(): array
    {
        return [
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'maintenance' => 'En mantenimiento',
        ];
    }

    public function getLocationOptions(): array
    {
        return [
            'interior' => 'Interior',
            'terraza' => 'Terraza',
            'barra' => 'Barra',
            'vip' => 'Zona VIP',
        ];
    }

    public function getStatusColor(string $status): string
    {
        return match($status) {
            'available' => 'success',
            'occupied' => 'danger',
            'reserved' => 'warning',
            'maintenance' => 'gray',
            default => 'primary',
        };
    }
}
