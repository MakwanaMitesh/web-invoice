<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'product_id',
        'quantity',
        'price',
        'amount',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
