<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Helpers;

class ProductResource extends Resource
{
  protected static ?string $model = Product::class;

  protected static ?string $navigationIcon = 'heroicon-o-cube';

  public static function form(Forms\Form $form): Forms\Form
  {
    return $form->schema([
      Forms\Components\TextInput::make('name')
        ->required()
        ->placeholder(__('Name'))
        ->maxLength(255),
      Forms\Components\TextInput::make('code')
        ->required()
        ->placeholder(__('Code'))
        ->maxLength(255),
      Forms\Components\Select::make('category_id')
        ->relationship('category', 'name')
        ->placeholder(__('Category'))
        ->searchable()
        ->required(),
      Forms\Components\TextInput::make('price')
        ->placeholder(__('Price'))
        ->numeric()
        ->required(),
      Forms\Components\Textarea::make('description')
        ->placeholder(__('Description'))
        ->rows(2)
        ->nullable(),
      SpatieMediaLibraryFileUpload::make('image')
        ->label('Image')
        ->collection('Product Images'),
    ]);
  }

  public static function table(Tables\Table $table): Tables\Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('image')
          ->label('Image')
          ->rounded()
          ->width(50)
          ->height(50),
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('code')
          ->sortable()
          ->toggleable()
          ->searchable(),
        Tables\Columns\TextColumn::make('category.name')
          ->label('Category')
          ->toggleable()
          ->sortable(),
        Tables\Columns\TextColumn::make('price')
          ->toggleable()
          ->sortable()
          ->getStateUsing(function ($record) {
            return formatCurrency($record->price);
          }),
      ])
      ->filters([
        // Add any filters if needed
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->actionsColumnLabel('Actions');
  }

  public static function getRelations(): array
  {
    return [
        // Define relationships if needed
      ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListProducts::route('/'),
      'create' => Pages\CreateProduct::route('/create'),
      'edit' => Pages\EditProduct::route('/{record}/edit'),
    ];
  }
}
