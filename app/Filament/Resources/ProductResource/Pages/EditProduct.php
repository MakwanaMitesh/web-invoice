<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
  protected static string $resource = ProductResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Action::make('back')
        ->url(static::getResource()::getUrl()) // or you can use url(static::getResource()::getUrl())
        ->button()
        ->color('info'),
      Actions\DeleteAction::make(),
    ];
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
