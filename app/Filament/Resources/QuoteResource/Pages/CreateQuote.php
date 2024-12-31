<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;

class CreateQuote extends CreateRecord
{
  protected static string $resource = QuoteResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Action::make('back')
        ->url(static::getResource()::getUrl()) // or you can use url(static::getResource()::getUrl())
        ->button(),
    ];
  }
}
