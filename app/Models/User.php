<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements FilamentUser, HasName
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable;

  protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'contact',
    'region_code',
    'status',
    'language',
    'password',
    'email_verified_at',
  ];

  protected $hidden = ['password', 'remember_token'];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function getFilamentName(): string
  {
    return "{$this->first_name} {$this->last_name}";
  }

  public function getFullNameAttribute(): string
  {
    return "{$this->first_name} {$this->last_name}";
  }

  public function canAccessPanel(Panel $panel): bool
  {
    // Customize the logic based on your application's requirements
    return true; // Example: allow all users access to the panel
  }

  public function address(): HasOne
  {
    return $this->hasOne(related: Address::class, foreignKey: 'user_id');
  }
}
