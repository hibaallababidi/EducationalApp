<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteSpecialization extends Model
{
    use HasFactory;

    protected $fillable=[
        'student_id',
        'specialization_id',
    ];
}
