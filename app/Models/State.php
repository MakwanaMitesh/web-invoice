<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'country_id',
        'name',
    ];

    protected $casts = [
        'country_id' => 'integer',
        'name'       => 'string',
    ];

    /**
     * @var array
     */
    public static $rules = [
        'name' => 'required|max:180|unique:states,name,',
        'country_id' => 'required',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
