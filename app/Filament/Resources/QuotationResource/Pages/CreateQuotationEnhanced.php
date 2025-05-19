<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class CreateQuotationEnhanced extends Page
{
    use InteractsWithForms;

    protected static string $resource = QuotationResource::class;

    protected static string $view = 'filament.resources.quotation-resource.pages.create-quotation-enhanced';

    // Propiedades para el formulario principal
    public ?array $data = [];

    // Propiedades para la selección de productos
    public Collection $products;
    public string $productSearchQuery = '';

    // Propiedades para el carrito
    public array $selectedProducts = [];
    public array $productQuantities = [];
    public array $productPrices = [];
    public array $productNotes = [];
    public array $productSubtotals = [];

    // Propiedades para totales
    public float $subtotal = 0;
    public float $tax = 0;
    public float $discount = 0;
    public float $total = 0;

    // Propiedades para modales
    public bool $showEditPriceModal = false;
    public bool $showAddNoteModal = false;
    public ?string $editingProductId = null;
    public ?float $editingPrice = null;
    public ?string $editingNote = null;

    public function mount(): void
    {
        $this->form->fill([
            'issue_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'status' => Quotation::STATUS_DRAFT,
            'payment_terms' => Quotation::PAYMENT_TERMS_CASH,
            'terms_and_conditions' => "1. Precios incluyen IGV.\n2. Cotización válida hasta la fecha indicada.\n3. Forma de pago según lo acordado.\n4. Tiempo de entrega a coordinar.",
        ]);

        // Cargar todos los productos activos
        $this->loadProducts();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Información General')
                            ->schema([
                                TextInput::make('quotation_number')
                                    ->label('Número de Cotización')
                                    ->default(fn () => Quotation::generateQuotationNumber())
                                    ->disabled()
                                    ->required(),

                                DatePicker::make('issue_date')
                                    ->label('Fecha de Emisión')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('valid_until')
                                    ->label('Válido Hasta')
                                    ->default(now()->addDays(15))
                                    ->required(),

                                Select::make('payment_terms')
                                    ->label('Términos de Pago')
                                    ->options([
                                        Quotation::PAYMENT_TERMS_CASH => 'Contado',
                                        Quotation::PAYMENT_TERMS_CREDIT_15 => 'Crédito 15 días',
                                        Quotation::PAYMENT_TERMS_CREDIT_30 => 'Crédito 30 días',
                                        Quotation::PAYMENT_TERMS_CREDIT_60 => 'Crédito 60 días',
                                    ])
                                    ->default(Quotation::PAYMENT_TERMS_CASH)
                                    ->required(),
                            ])
                            ->columns(2),

                        Section::make('Cliente')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),

                                        Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options([
                                                'DNI' => 'DNI',
                                                'RUC' => 'RUC',
                                                'CE' => 'Carnet de Extranjería',
                                                'Pasaporte' => 'Pasaporte',
                                            ])
                                            ->required(),

                                        TextInput::make('document_number')
                                            ->label('Número de Documento')
                                            ->required()
                                            ->maxLength(20),

                                        TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(20),

                                        TextInput::make('email')
                                            ->label('Correo Electrónico')
                                            ->email()
                                            ->maxLength(255),

                                        TextInput::make('address')
                                            ->label('Dirección')
                                            ->maxLength(255),
                                    ])
                                    ->required(),
                            ]),

                        Section::make('Notas y Condiciones')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->placeholder('Notas adicionales para el cliente')
                                    ->maxLength(500),

                                Textarea::make('terms_and_conditions')
                                    ->label('Términos y Condiciones')
                                    ->placeholder('Términos y condiciones de la cotización')
                                    ->default("1. Precios incluyen IGV.\n2. Cotización válida hasta la fecha indicada.\n3. Forma de pago según lo acordado.\n4. Tiempo de entrega a coordinar.")
                                    ->maxLength(1000),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->statePath('data');
    }

    public function loadProducts(): void
    {
        $query = Product::where('active', true);

        if ($this->productSearchQuery) {
            $query->where('name', 'like', "%{$this->productSearchQuery}%");
        }

        $this->products = $query->orderBy('name')->get();
    }

    public function updatedProductSearchQuery(): void
    {
        $this->loadProducts();
    }

    public function addProduct(string $productId): void
    {
        if (!in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts[] = $productId;

            $product = Product::find($productId);
            $this->productQuantities[$productId] = 1;
            $this->productPrices[$productId] = $product->price;
            $this->productNotes[$productId] = '';
            $this->productSubtotals[$productId] = $product->price;

            $this->calculateTotal();
        }
    }

    public function removeProduct(string $productId): void
    {
        $index = array_search($productId, $this->selectedProducts);
        if ($index !== false) {
            unset($this->selectedProducts[$index]);
            $this->selectedProducts = array_values($this->selectedProducts);

            unset($this->productQuantities[$productId]);
            unset($this->productPrices[$productId]);
            unset($this->productNotes[$productId]);
            unset($this->productSubtotals[$productId]);

            $this->calculateTotal();
        }
    }

    public function updateQuantity(string $productId, int $quantity): void
    {
        if ($quantity < 1) $quantity = 1;

        $this->productQuantities[$productId] = $quantity;
        $this->updateSubtotal($productId);
    }

    public function openEditPriceModal(string $productId): void
    {
        $this->editingProductId = $productId;
        $this->editingPrice = $this->productPrices[$productId];
        $this->showEditPriceModal = true;
    }

    public function savePrice(): void
    {
        if ($this->editingProductId && $this->editingPrice !== null) {
            $this->productPrices[$this->editingProductId] = $this->editingPrice;
            $this->updateSubtotal($this->editingProductId);

            $this->showEditPriceModal = false;
            $this->editingProductId = null;
            $this->editingPrice = null;
        }
    }

    public function openAddNoteModal(string $productId): void
    {
        $this->editingProductId = $productId;
        $this->editingNote = $this->productNotes[$productId];
        $this->showAddNoteModal = true;
    }

    public function saveNote(): void
    {
        if ($this->editingProductId) {
            $this->productNotes[$this->editingProductId] = $this->editingNote;

            $this->showAddNoteModal = false;
            $this->editingProductId = null;
            $this->editingNote = null;
        }
    }

    public function updateSubtotal(string $productId): void
    {
        $quantity = $this->productQuantities[$productId];
        $price = $this->productPrices[$productId];
        $this->productSubtotals[$productId] = $quantity * $price;

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->subtotal = array_sum($this->productSubtotals);
        $this->tax = $this->subtotal * 0.18; // 18% IGV
        $this->total = $this->subtotal + $this->tax - $this->discount;
    }

    public function updateDiscount(float $discount): void
    {
        $this->discount = $discount;
        $this->calculateTotal();
    }

    public function create(): void
    {
        $this->validate();

        if (empty($this->selectedProducts)) {
            Notification::make()
                ->title('Error')
                ->body('Debe agregar al menos un producto a la cotización')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Crear la cotización
            $quotation = new Quotation();
            $quotation->quotation_number = Quotation::generateQuotationNumber();
            $quotation->customer_id = $this->data['customer_id'];
            $quotation->user_id = Auth::id();
            $quotation->issue_date = $this->data['issue_date'];
            $quotation->valid_until = $this->data['valid_until'];
            $quotation->status = Quotation::STATUS_DRAFT;
            $quotation->payment_terms = $this->data['payment_terms'];
            $quotation->notes = $this->data['notes'] ?? null;
            $quotation->terms_and_conditions = $this->data['terms_and_conditions'] ?? null;
            $quotation->subtotal = $this->subtotal;
            $quotation->tax = $this->tax;
            $quotation->discount = $this->discount;
            $quotation->total = $this->total;
            $quotation->save();

            // Crear los detalles de la cotización
            foreach ($this->selectedProducts as $productId) {
                $detail = new QuotationDetail();
                $detail->quotation_id = $quotation->id;
                $detail->product_id = $productId;
                $detail->quantity = $this->productQuantities[$productId];
                $detail->unit_price = $this->productPrices[$productId];
                $detail->subtotal = $this->productSubtotals[$productId];
                $detail->notes = $this->productNotes[$productId];
                $detail->save();
            }

            DB::commit();

            Notification::make()
                ->title('Cotización creada')
                ->body('La cotización ha sido creada correctamente')
                ->success()
                ->send();

            // Redirigir a la vista de la cotización
            redirect('/admin/ventas/cotizaciones/' . $quotation->id);
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Ha ocurrido un error al crear la cotización: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Crear Cotización')
                ->submit('create'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url('/admin/ventas/cotizaciones')
                ->color('gray'),
        ];
    }
}
