<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
  use HasFactory;

  protected $casts = [
    'name' => 'string',
    'short_code' => 'string',
    'phone_code' => 'string',
  ];

  protected $table = 'countries';

  /**
   * @var array
   */
  protected $fillable = ['short_code', 'name', 'phone_code'];

  /**
   * @var array
   */
  public static $rules = [
    'name' => 'required|max:180|unique:countries,name,',
    'short_code' => 'required|alpha|unique:countries,short_code,',
    'phone_code' => 'nullable|numeric|unique:countries,phone_code,',
  ];

  public function addresses()
  {
    return $this->hasMany(Address::class);
  }
}
