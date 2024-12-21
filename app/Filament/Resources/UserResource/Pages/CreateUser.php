<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Address;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
  protected static string $resource = UserResource::class;

  public static $label = 'Clients';

  public function getTitle(): string
  {
    return 'Create Client';
  }

  protected function handleRecordCreation(array $data): Model
  {
    $record = static::getModel()::create($data);
    $address = new Address();

    $address->address_1 = $data['address']['address_1'];
    $address->address_2 = $data['address']['address_2'] ?? null;
    $address->country_id = $data['address']['country_id'];
    $address->state_id = $data['address']['state_id'];
    $address->city_id = $data['address']['city_id'];
    $address->zip_code = $data['address']['zip_code'];
    $address->user_id = $record->id;

    $address->save();

    return $record;
  }

  protected function getRedirectUrl(): string
  {
      return $this->getResource()::getUrl('index');
  }
}
