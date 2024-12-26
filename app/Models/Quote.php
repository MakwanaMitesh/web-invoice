<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'quote_code',
    'quote_date',
    'due_date',
    'status',
    'template_id',
    'discount',
    'discount_type',
    'final_amount',
    'note',
    'term',
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
