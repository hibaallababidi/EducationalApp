<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable=[
        'course_name',
        'course_description',
        'is_free',
        'price',
        'status',
        'teacher_id'
    ];
}
