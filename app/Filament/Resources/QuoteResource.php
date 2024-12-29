<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Product;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Actions\Action;

class QuoteResource extends Resource
{
  protected static ?string $model = Quote::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Group::make()
        ->schema([
          // Previous Quote Detail Section remains the same...
          Forms\Components\Section::make('Quote Details')
            ->schema([
              Forms\Components\Select::make('user_id')
                ->preload()
                ->required()
                ->relationship(
                  name: 'user',
                  modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name')
                )
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                ->searchable(['first_name', 'last_name']),

              Forms\Components\TextInput::make('quote_name')
                ->required()
                ->placeholder('Quote Title')
                ->label('Quote Title'),

              Forms\Components\TextInput::make('quote_code')
                ->required()
                ->unique()
                ->placeholder('Quote Code')
                ->label('Quote Code #')
                ->default(fn() => strtoupper(Str::random(6)))
                ->prefix('QT-')
                ->suffixAction(
                  Forms\Components\Actions\Action::make('refreshCode')
                    ->label('Refresh Code')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn($set) => $set('quote_code', strtoupper(Str::random(6))))
                ),

              Forms\Components\DatePicker::make('quote_date')
                ->native(false)
                ->label('Quote Date')
                ->placeholder('Quote Date')
                ->displayFormat('d/m/Y'),

              Forms\Components\DatePicker::make('due_date')
                ->native(false)
                ->label('Due Date')
                ->placeholder('Due Date')
                ->displayFormat('d/m/Y'),

              Forms\Components\Textarea::make('note')->placeholder('Note'),
              Forms\Components\Textarea::make('term')
                ->label('Term & Condition')
                ->placeholder('Term & Condition'),
            ])
            ->columns(2),

          Forms\Components\Section::make('Product Details')->schema([static::getItemsRepeater()]),

          Forms\Components\Section::make('Pricing Summary')->schema([
            Forms\Components\Grid::make(4) // Changed to 4 columns
              ->schema([
                Forms\Components\Select::make('discount_type')
                  ->label('Discount Type')
                  ->options([
                    'percentage' => 'Percentage (%)',
                    'flat' => 'Flat Amount',
                  ])
                  ->searchable()
                  ->default('percentage')
                  ->reactive()
                  ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                    $set('discount_value', 0);
                    $set('discount_amount', 0);
                    $subtotal = $get('subtotal');
                    $set('amount', $subtotal);
                  }),

                Forms\Components\TextInput::make('discount_value')
                  ->label(
                    fn(Forms\Get $get) => $get('discount_type') === 'percentage' ? 'Discount (%)' : 'Discount Amount'
                  )
                  ->numeric()
                  ->default(0)
                  ->reactive()
                  ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                    $subtotal = floatval($get('subtotal'));
                    $discountType = $get('discount_type');
                    $discountValue = floatval($state);

                    if ($discountType === 'percentage') {
                      $discountValue = min(100, max(0, $discountValue));
                      $discountAmount = $subtotal * ($discountValue / 100);
                    } else {
                      $discountValue = min($subtotal, max(0, $discountValue));
                      $discountAmount = $discountValue;
                    }

                    if (floatval($state) !== $discountValue) {
                      $set('discount_value', $discountValue);
                    }

                    $set('discount_amount', round($discountAmount, 2));
                    $finalAmount = $subtotal - $discountAmount;
                    $set('amount', max(0, $finalAmount));
                  }),

                Forms\Components\TextInput::make('discount_amount')
                  ->label('Discount Amount')
                  ->numeric()
                  ->disabled()
                  ->default(0)
                  ->prefix(getCurrencySymbol()),

                Forms\Components\TextInput::make('subtotal')
                  ->label('Subtotal')
                  ->numeric()
                  ->prefix(getCurrencySymbol())
                  ->disabled()
                  ->reactive()
                  ->default(0),
              ]),

            Forms\Components\TextInput::make('amount')
              ->label('Total Amount')
              ->numeric()
              ->disabled()
              ->reactive()
              ->default(0)
              ->prefix(getCurrencySymbol())
              ->columnSpanFull(),
          ]),
        ])
        ->columnSpan(['lg' => 2]),
    ]);
  }

  public static function getItemsRepeater(): Forms\Components\Repeater
  {
    return Forms\Components\Repeater::make('quoteItems')
      ->relationship()
      ->reactive()
      ->schema([
        Forms\Components\Select::make('product_id')
          ->label('Product')
          ->options(Product::query()->pluck('name', 'id'))
          ->required()
          ->reactive()
          ->disableOptionsWhenSelectedInSiblingRepeaterItems()
          ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
            if ($state) {
              $product = Product::find($state);
              if ($product) {
                $set('price', $product->price);
                $set('quantity', 1);
                $set('total', $product->price);
              }
            }

            static::recalculateAmounts($set, $get);
          })
          ->columnSpan(['md' => 5])
          ->searchable(),

        Forms\Components\TextInput::make('quantity')
          ->label('Quantity')
          ->numeric()
          ->reactive()
          ->placeholder(0)
          ->minValue(1)
          ->required()
          ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
            $price = floatval($get('price'));
            $quantity = floatval($state ?? 1);
            $amount = $price * $quantity;
            $set('total', $amount);

            static::recalculateAmounts($set, $get);
          })
          ->columnSpan(['md' => 2]),

        Forms\Components\TextInput::make('price')
          ->label('Unit Price')
          ->numeric()
          ->minValue(1)
          ->required()
          ->prefix(getCurrencySymbol())
          ->placeholder(0)
          ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
            $quantity = floatval($get('quantity'));
            $price = floatval($state);
            $amount = $quantity * $price;
            $set('total', $amount);

            static::recalculateAmounts($set, $get);
          })
          ->columnSpan(['md' => 3]),

        Forms\Components\TextInput::make('total')
          ->label('Total')
          ->numeric()
          ->prefix(getCurrencySymbol())
          ->placeholder(0)
          ->disabled(true)
          ->dehydrated(true)
          ->inputMode('none')
          ->columnSpan(['md' => 3]),
      ])
      ->defaultItems(1)
      ->required()
      ->columns(['md' => 13])
      ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
        $quantity = floatval($get('quantity'));
        $price = floatval($state);
        $amount = $quantity * $price;
        $set('total', $amount);
        static::recalculateAmounts($set, $get);
      });
  }

  // New helper method to handle all calculations
  protected static function recalculateAmounts(Forms\Set $set, Forms\Get $get): void
  {
    $allData = $get();
    $quoteItems = $allData['quoteItems'] ?? [];

    $quoteItems = $get('../../quoteItems') ?? [];

    $quoteItemsNew = $allData['quoteItems'] ?? [];

    foreach ($quoteItemsNew as $key => &$item) {
      $item['quantity'] = $item['quantity'] ?? 1; // Default quantity to 1 if null
      $item['price'] = $item['price'] ?? 0; // Default price to 0 if null
      $item['total'] = $item['quantity'] * $item['price']; // Recalculate total
    }

    if (empty($quoteItems)) {
      $quoteItems = $quoteItemsNew;
    }

    $subtotal = collect($quoteItems)->sum(function ($item) {
      return floatval($item['quantity'] ?? 0) * floatval($item['price'] ?? 0);
    });

    $set('../../subtotal', $subtotal);
    $set('subtotal', $subtotal);

    $discountType = $get('../../discount_type');
    $discountValue = floatval($get('../../discount_value') ?? 0);

    // Calculate discount
    if ($discountType === 'percentage') {
      $discountValue = min(100, max(0, $discountValue));
      $discountAmount = $subtotal * ($discountValue / 100);
    } else {
      $discountValue = min($subtotal, max(0, $discountValue));
      $discountAmount = $discountValue;
    }

    // Set discount amounts
    $set('../../discount_amount', round($discountAmount, 2));

    // Calculate final amount
    $finalAmount = max(0, $subtotal - $discountAmount);
    $set('../../amount', $finalAmount);
    $set('amount', $finalAmount);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('user_id')
          ->numeric()
          ->sortable(),
      ])
      ->actions([Tables\Actions\EditAction::make()])
      ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListQuotes::route('/'),
      'create' => Pages\CreateQuote::route('/create'),
      'edit' => Pages\EditQuote::route('/{record}/edit'),
    ];
  }
}
