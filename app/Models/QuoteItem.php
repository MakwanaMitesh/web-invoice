<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
  use HasFactory;

  protected $fillable = ['quote_id', 'product_id', 'product_name','quantity', 'price', 'total'];

  public function quote()
  {
    return $this->belongsTo(Quote::class);
  }

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id', 'id');
  }
}
