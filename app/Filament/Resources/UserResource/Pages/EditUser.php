<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditUser extends EditRecord
{
  public static $label = 'Clients';

  public function getTitle(): string
  {
    return 'Edit Client';
  }
  protected static string $resource = UserResource::class;

  protected function getHeaderActions(): array
  {
    return [Action::make('back')
    ->url(static::getResource()::getUrl()) // or you can use url(static::getResource()::getUrl())
    ->button()
    ->color('info'),Actions\DeleteAction::make()];
  }

  protected function getRedirectUrl(): string
  {
      return $this->getResource()::getUrl('index');
  }
}
