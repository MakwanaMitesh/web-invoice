<?php
namespace App\Filament\Resources;

use App\Models\Tax;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use App\Filament\Resources\TaxResource\Pages;

class TaxResource extends Resource
{
  protected static ?string $model = Tax::class;

  protected static ?string $navigationLabel = 'Taxes';
  protected static ?string $navigationIcon = 'heroicon-o-percent-badge';

  public static function form(Forms\Form $form): Forms\Form
  {
    return $form->schema([
      Forms\Components\TextInput::make('name')
        ->label('Name')
        ->unique(ignoreRecord: true)
        ->maxLength(255)
        ->placeholder(__('Name'))
        ->required()
        ->maxLength(255),
      Forms\Components\TextInput::make('value')
        ->label('Tax Value')
        ->placeholder(__('Tax Value'))
        ->required()
        ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
        ->minValue(0)
        ->numeric(),
      Forms\Components\Toggle::make('is_default')
        ->label('Is Default')
        ->inline(false)
        ->default(false),
    ]);
  }

  public static function table(Tables\Table $table): Tables\Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->sortable()
          ->searchable()
          ->label('Tax Name'),

        TextColumn::make('value')
          ->sortable()
          ->label('Tax Value')
          ->formatStateUsing(fn($state) => number_format($state, 2)),

        Tables\Columns\ToggleColumn::make('is_default')
          ->beforeStateUpdated(function (Tax $record) {
              Tax::where('id', '!=', $record->id)->update(['is_default' => false]);
          })
          ->label('Is Default')
          ->sortable()
          ->toggleable(),

          Tables\Columns\TextColumn::make('updated_at')
          ->label('Updated Date')
          ->date()
          ->searchable()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        // You can add filters here if necessary
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
        // Add any relationships if needed
      ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListTaxes::route('/'),
      // 'create' => Pages\CreateTax::route('/create'),
      // 'edit' => Pages\EditTax::route('/{record}/edit'),
    ];
  }
}
