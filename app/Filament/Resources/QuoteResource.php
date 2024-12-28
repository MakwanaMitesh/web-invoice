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

class QuoteResource extends Resource
{
  protected static ?string $model = Quote::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Group::make()
        ->schema([
          // Quote Detail Section
          Forms\Components\Section::make('Quote Detail')
            ->schema([
              Forms\Components\Select::make('user_id')
                ->relationship(
                  name: 'user',
                  modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name')
                )
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                ->preload()
                ->required()
                ->searchable(['first_name', 'last_name']),

              Forms\Components\TextInput::make('quote_code')
                ->required()
                ->unique()
                ->label('Quote Code #')
                ->default(fn() => strtoupper(Str::random(6)))
                ->suffixAction(
                  Forms\Components\Actions\Action::make('refreshCode')
                    ->label('Refresh Code')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn($set) => $set('quote_code', strtoupper(Str::random(6))))
                ),

              Forms\Components\DatePicker::make('quote_date')
                ->native(false)
                ->placeholder('Quote Date')
                ->displayFormat('d/m/Y'),

              Forms\Components\DatePicker::make('due_date')
                ->native(false)
                ->placeholder('Due Date')
                ->displayFormat('d/m/Y'),

              Forms\Components\Textarea::make('note'),
              Forms\Components\Textarea::make('term'),
            ])
            ->collapsible()
            ->columns(2),

          // Product Detail Section
          Forms\Components\Section::make('Product Detail')
            ->schema([
              static::getItemsRepeater(), // Repeater for products
              Forms\Components\TextInput::make('final_amount')
                ->label('Final Amount')
                ->numeric()
                ->disabled()
                ->default(0),
            ])
            ->collapsible(),
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
                $set('amount', $product->price);
              }
            }

            // Recalculate the final amount after product change
            $quoteItems = $get('../../quoteItems');
            $finalTotal = collect($quoteItems)->sum(function ($item) {
              return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
            });
            $set('../../final_amount', $finalTotal);
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
            $price = $get('price');
            $quantity = $state ?? 1;
            $amount = $price * $quantity;
            $set('amount', $amount);

            // Recalculate the final amount after quantity change
            $quoteItems = $get('../../quoteItems');
            $finalTotal = collect($quoteItems)->sum(function ($item) {
              return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
            });
            $set('../../final_amount', $finalTotal);
          })
          ->columnSpan(['md' => 2]),

        Forms\Components\TextInput::make('price')
          ->label('Unit Price')
          ->numeric()
          ->minValue(1)
          ->required()
          ->placeholder(0)
          ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
            $quantity = $get('quantity');
            $amount = $quantity * $state;
            $set('amount', $amount);

            // Recalculate the final amount after price change
            $quoteItems = $get('../../quoteItems');
            $finalTotal = collect($quoteItems)->sum(function ($item) {
              return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
            });
            $set('../../final_amount', $finalTotal);
          })
          ->columnSpan(['md' => 3]),

        Forms\Components\TextInput::make('amount')
          ->label('Total')
          ->numeric()
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
        // Recalculate the final amount whenever an item is deleted
        $quoteItems = $get('quoteItems') ?? [];
        $finalTotal = collect($quoteItems)->sum(function ($item) {
          return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
        });
        $set('final_amount', $finalTotal);
      });
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
