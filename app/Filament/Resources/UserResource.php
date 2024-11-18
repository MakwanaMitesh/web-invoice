<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Clients';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
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

                        Forms\Components\FileUpload::make('attachments')
                          ->label(label: 'Profile Image'),

                        ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->orderBy('last_name', $direction)
                            ->orderBy('first_name', $direction);
                    })
                    ->searchable(['first_name', 'last_name'])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
