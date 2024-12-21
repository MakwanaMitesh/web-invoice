<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class CategoryResource extends Resource
{
  protected static ?string $model = Category::class;

  protected static ?string $navigationIcon = 'heroicon-o-table-cells';

  public static function form(Forms\Form $form): Forms\Form
  {
    return $form->schema([
      Forms\Components\TextInput::make('name')
        ->required()
        ->unique(ignoreRecord: true)
        ->maxLength(255)
        ->columnSpan('full'),
    ]);
  }

  public static function table(Tables\Table $table): Tables\Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->searchable(),
        // Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListCategories::route('/'),
    ];
  }

  protected function getActions(): array
  {
    return [\Filament\Actions\CreateAction::make()->createAnother(false)];
  }
}
