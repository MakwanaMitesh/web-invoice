<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasName, HasMedia
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, InteractsWithMedia;

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

  const PROFILE = 'Profile Images';

  protected $appends = ['profile_image', 'full_name'];

  /**
   * @return string
   */
  public function getProfileImageAttribute(): string
  {
    /** @var Media $media */
    $media = $this->getMedia(self::PROFILE)->first();
    if (!empty($media)) {
      return $media->getFullUrl();
    }

    return '';
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
    return $this->hasOne(Address::class, 'user_id');
  }

  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('avatars')->singleFile(); // Optional: allows only one file for the avatar
  }
}
