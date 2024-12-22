<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\City;
use App\Models\State;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use App\Filament\Exports\ProductExporter;
use Filament\Tables\Actions\ExportAction;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static ?string $navigationIcon = 'heroicon-o-user-group';

  protected static ?string $clusterBreadcrumb = 'Clients';

  protected static ?string $navigationLabel = 'Clients';

  public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
  {
    return parent::getEloquentQuery()->with('address');
  }
  public static function form(Form $form): Form
  {
    return $form->schema([
      Forms\Components\Group::make()
        ->schema([
          Forms\Components\Section::make('Client Detail')
            ->schema([
              Forms\Components\TextInput::make('first_name')
                ->placeholder(__('First Name'))
                ->maxLength(255)
                ->required(),
              Forms\Components\TextInput::make('last_name')
                ->placeholder('Last Name')
                ->maxLength(255)
                ->required(),
              Forms\Components\TextInput::make('email')
                ->placeholder(placeholder: 'Email')
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->required(),
              Forms\Components\TextInput::make('password')
                ->password()
                ->hiddenOn(operations: 'edit')
                ->placeholder('Password')
                ->required()
                ->revealable()
                ->minLength(8)
                ->label('Password'),
              Forms\Components\TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->placeholder('Confirm Password')
                ->password()
                ->hiddenOn(operations: 'edit')
                ->required()
                ->revealable()
                ->same('password')
                ->minLength(8),
              Forms\Components\Toggle::make('status')
                ->label('Status')
                ->inline(false)
                ->default(state: true)
                ->extraAttributes(['class' => 'toggle-with-label']),
              SpatieMediaLibraryFileUpload::make('profile_image')
                ->label('Profile Image')
                ->collection('Profile Images'),
            ])
            ->columns(2),

          Forms\Components\Section::make('Address Detail')
            ->schema([
              Forms\Components\TextInput::make('address.address_1')
                ->placeholder(__('Address Line 1'))
                ->maxLength(255)
                ->required()
                ->label('Address Line 1'),

              Forms\Components\TextInput::make('address.address_2')
                ->placeholder(__('Address Line 2'))
                ->maxLength(255)
                ->label('Address Line 1'),
              Forms\Components\Select::make('address.country_id') // Relation field for Address Country
                ->label('Country')
                ->relationship('address.country', 'name') // Assuming 'address' is the relationship method in User
                ->searchable()
                ->preload()
                ->placeholder(__('Country'))
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                  $set('address.state_id', null); // Reset state and city when country changes
                  $set('address.city_id', null);
                })
                ->required(),

              Forms\Components\Select::make('address.state_id')
                ->label('State')
                ->options(
                  fn(Get $get): Collection => State::query()
                    ->where('country_id', $get('address.country_id'))
                    ->pluck('name', 'id')
                )
                ->searchable()
                ->placeholder(__('State'))
                ->preload()
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                  $set('address.city_id', null); // Reset city when state changes
                })
                ->required(),

              Forms\Components\Select::make('address.city_id') // Relation field for Address City
                ->label('City')
                ->options(
                  fn(Get $get): Collection => City::query() // You need to change this based on your city model
                    ->where('state_id', $get('address.state_id'))
                    ->pluck('name', 'id')
                )
                ->searchable()
                ->placeholder(__('City'))
                ->preload()
                ->live()
                ->required(),
              Forms\Components\TextInput::make(name: 'address.zip_code')
                ->placeholder(__('Zip Code'))
                ->maxLength(255)
                ->numeric()
                ->required(),
            ])
            ->columns(2),
        ])
        ->columnSpan(['lg' => 2]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('profile_image')
          ->label('Profile Image')
          ->rounded()
          ->width(50)
          ->height(50),
        TextColumn::make('full_name')
          ->sortable(
            query: function (Builder $query, string $direction): Builder {
              return $query->orderBy('first_name', $direction)->orderBy('last_name', $direction);
            }
          )
          ->searchable(['first_name', 'last_name']),
        Tables\Columns\TextColumn::make('email')
          ->searchable()
          ->sortable()
          ->toggleable(),
        Tables\Columns\ToggleColumn::make('status')
          ->label('Status')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Created Date')
          ->date()
          ->searchable()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->headerActions([
        ExportAction::make()
          ->label('Export clients')
          ->exporter(ProductExporter::class),
      ])
      ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
      ->actionsColumnLabel('Actions');
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListUsers::route('/'),
      'create' => Pages\CreateUser::route('/create'),
      'edit' => Pages\EditUser::route('/{record}/edit'),
    ];
  }

  public static function mutateFormDataBeforeSave(array $data): array
  {
    if (isset($data['address'])) {
      $addressData = $data['address'];
      unset($data['address']);
      request()->merge(['address' => $addressData]);
    }

    return $data;
  }

  public static function afterSave($record): void
  {
    if (request()->has('address')) {
      $record->address()->updateOrCreate([], request('address'));
    }
  }
}
