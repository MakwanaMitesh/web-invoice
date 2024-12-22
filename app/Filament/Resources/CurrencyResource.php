<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Filament\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyResource extends Resource
{
  protected static ?string $model = Currency::class;

  protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\TextInput::make('name')
        ->placeholder('Name')
        ->required()
        ->unique(ignoreRecord: true)
        ->maxLength(255),
      Forms\Components\TextInput::make('icon')
        ->placeholder('Icon')
        ->required()
        ->maxLength(10),
      Forms\Components\TextInput::make('code')
        ->placeholder('Code')
        ->required()
        ->unique(ignoreRecord: true)
        ->length(3),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('icon')->sortable(),
        Tables\Columns\TextColumn::make('code')
          ->sortable()
          ->searchable(),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->actionsColumnLabel('Actions')
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
      'index' => Pages\ListCurrencies::route('/'),
      // 'create' => Pages\CreateCurrency::route('/create'),
      // 'edit' => Pages\EditCurrency::route('/{record}/edit'),
    ];
  }
}
