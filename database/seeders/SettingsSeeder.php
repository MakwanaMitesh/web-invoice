<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
  public function run()
  {
    Setting::updateOrCreate(['key' => 'app_name'], ['value' => 'InvoicePlane']);

    Setting::updateOrCreate(['key' => 'currency_id'], ['value' => '1']);

    Setting::updateOrCreate(['key' => 'decimal_separator'], ['value' => '.']);

    Setting::updateOrCreate(['key' => 'thousand_separator'], ['value' => ',']);

    Setting::updateOrCreate(['key' => 'number_of_decimals'], ['value' => '2']);

    Setting::updateOrCreate(['key' => 'currency_position'], ['value' => 'prefix']);

    Setting::updateOrCreate(['key' => 'space_between_symbol'], ['value' => 'true']);

    Setting::updateOrCreate(['key' => 'symbol_case'], ['value' => 'uppercase']);

    Setting::updateOrCreate(['key' => 'rounding_method'], ['value' => '3']);
  }
}
