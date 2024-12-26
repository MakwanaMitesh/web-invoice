<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\City;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Str;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use App\Filament\Exports\ProductExporter;
use Filament\Tables\Actions\ExportAction;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class QuoteResource extends Resource
{
  protected static ?string $model = Quote::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Group::make()
        ->schema([
          Forms\Components\Section::make('Quote Detail')
            ->schema([
              Forms\Components\Select::make('user_id')
                ->relationship(
                  name: 'user',
                  modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name')
                )
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->first_name} {$record->last_name}")
                ->preload()
                ->searchable(['first_name', 'last_name']),

                Forms\Components\TextInput::make('quote_code')
                ->required()
                ->unique(true)
                ->label('Quote Code #') // Correctly spelled label
                ->default(fn() => strtoupper(Str::random(6)))
                ->minLength(6) // Ensures at least 6 characters
                ->maxLength(6) // Ensures no more than 6 characters
                ->suffixAction(
                    Forms\Components\Actions\Action::make('refreshCode')
                        ->label('Refresh Code')
                        ->icon('heroicon-o-arrow-path') // Uses a refresh icon from Heroicons
                        ->action(fn($get, $set) => $set('quote_code', strtoupper(Str::random(6))))
                ),
              Forms\Components\DatePicker::make('quote_date'),
              Forms\Components\DatePicker::make('due_date'),
              Forms\Components\TextInput::make('status')
                ->required()
                ->maxLength(255)
                ->default('draft'),
              Forms\Components\TextInput::make('template_id')->numeric(),
              Forms\Components\TextInput::make('discount')->numeric(),
              Forms\Components\TextInput::make('discount_type')->maxLength(255),
              Forms\Components\TextInput::make('final_amount')->numeric(),
              Forms\Components\Textarea::make('note'),
              Forms\Components\Textarea::make('term'),
            ])
            ->columns(2),

          Forms\Components\Section::make('Product Detail')
            ->schema([])
            ->columns(2),
        ])
        ->columnSpan(['lg' => 2]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('user_id')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('quote_code')->searchable(),
        Tables\Columns\TextColumn::make('quote_date')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('due_date')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('status')->searchable(),
        Tables\Columns\TextColumn::make('template_id')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('discount')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('discount_type')->searchable(),
        Tables\Columns\TextColumn::make('final_amount')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->actions([Tables\Actions\EditAction::make()])
      ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
  }

  public static function getRelations(): array
  {
    return [
        //
      ];
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
