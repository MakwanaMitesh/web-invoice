<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
  public static $label = 'Clients';

  public function getTitle(): string
  {
    return 'Create Client';
  }
  protected static string $resource = UserResource::class;

  protected function getHeaderActions(): array
  {
    return [Actions\DeleteAction::make()];
  }

  protected function mutateFormDataBeforeFill(array $data): array
  {
    $data['user_id'] = auth()->id();

    dd($data);
    return $data;
  }
}
