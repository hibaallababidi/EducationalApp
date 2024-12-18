<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['city_name'];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'city_id', 'id');
    }
}
