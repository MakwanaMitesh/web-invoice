<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    /**
     * @var array
     */
    protected $fillable = [
        'state_id',
        'name',
    ];

    protected $casts = [
        'state_id' => 'integer',
        'name' => 'string',
    ];

    /**
     * @var array
     */
    public static $rules = [
        'name' => 'required|max:180|unique:cities,name,',
        'state_id' => 'required',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
