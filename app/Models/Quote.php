<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'quote_name',
    'quote_code',
    'quote_date',
    'due_date',
    'status',
    'template_id',
    'discount',
    'discount_type',
    'discount_amount',
    'subtotal',
    'amount',
    'note',
    'term',
  ];

  const DRAFT = 0;
  const CONVERTED = 1;
  const STATUS_ALL = 2;

  const STATUS_ARR = [
      self::DRAFT => 'Draft',
      self::CONVERTED => 'Converted',
      self::STATUS_ALL => 'All',
  ];

  public function quoteItems()
  {
    return $this->hasMany(QuoteItem::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function getUserFullNameAttribute()
  {
      return $this->user ? $this->user->full_name : null;
  }
}
