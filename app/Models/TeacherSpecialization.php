<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSpecialization extends Model
{
    use HasFactory;

    protected $fillable=[
        'teacher_id',
        'specialization_id'
    ];
}
