<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
  use HasFactory, InteractsWithMedia;

  protected $fillable = ['name', 'code', 'category_id', 'price', 'description'];

  const IMAGE = 'Product Images';

  protected $appends = ['image'];

  /**
   * @return string
   */
  public function getImageAttribute(): string
  {
    /** @var Media $media */
    $media = $this->getMedia(self::IMAGE)->first();
    if (!empty($media)) {
      return $media->getFullUrl();
    }

    return '';
  }

  public function category()
  {
    return $this->belongsTo(related: Category::class);
  }
}
